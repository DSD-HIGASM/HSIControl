<?php

namespace App\Console\Commands;

use App\Mail\AcademicAgentExpiringMail;
use App\Models\AcademicAgent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireAcademicUsersCommand extends Command
{
    protected $signature = 'hsi:expire-academic-users';
    protected $description = 'Notifica próximos vencimientos a docentes UNLP y alerta al equipo sobre accesos a revocar.';

    public function handle()
    {
        $today = Carbon::today();
        $warningLimit = Carbon::today()->addDays(30);

        // 1. AVISO A DOCENTES (Faltan 30 días para el vencimiento)
        $expiringAgents = AcademicAgent::where('hsi_status', 'activo')
            ->whereBetween('expires_at', [$today, $warningLimit])
            ->where('expiration_warning_sent', false)
            ->get();

        $warningCount = 0;

        foreach ($expiringAgents as $acad) {
            try {
                Mail::to($acad->unlp_email)
                    ->cc($acad->personal_email)
                    ->send(new AcademicAgentExpiringMail($acad));

                $acad->update(['expiration_warning_sent' => true]);
                $warningCount++;
                Log::info("CRON HSI UNLP: Mail por vencer enviado a {$acad->unlp_email}");
            } catch (\Throwable $e) {
                Log::error("CRON HSI UNLP: Error enviando aviso a {$acad->unlp_email}: " . $e->getMessage());
            }
        }

        // 2. ALERTA A OPERADORES (Llegaron a la fecha de vencimiento hoy sin renovar)
        $expiredToday = AcademicAgent::where('hsi_status', 'activo')
            ->whereDate('expires_at', '<=', $today)
            ->get();

        if ($expiredToday->isNotEmpty()) {
            try {
                // Dirección donde el equipo recibe alertas operativas
                $supportEmail = config('mail.from.address', 'saluddigital@higa.gba.gov.ar');

                Mail::raw($this->buildSupportEmailBody($expiredToday), function ($message) use ($supportEmail) {
                    $message->to($supportEmail)
                            ->subject('⚠️ Alerta HSI UNLP: Docentes con vigencia vencida para revisión');
                });

                Log::info("CRON HSI UNLP: Mail de reporte de bajas enviado al equipo.");
            } catch (\Throwable $e) {
                Log::error("CRON HSI UNLP: Error enviando alerta al equipo: " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado. Avisos a docentes: {$warningCount}. Requieren baja/revisión: {$expiredToday->count()}.");
        return Command::SUCCESS;
    }

    private function buildSupportEmailBody($agents): string
    {
        $body = "REPORTE DIARIO DE VENCIMIENTOS HSI — UNLP\n";
        $body .= "Fecha: " . now()->format('d/m/Y') . "\n";
        $body .= "--------------------------------------------------------\n";
        $body .= "Los siguientes docentes han alcanzado su fecha de expiración y su acceso continúa activo. Por favor, revisar y proceder con la revocación si no presentaron renovación:\n\n";

        foreach ($agents as $acad) {
            $nombre = $acad->agent->first_name . ' ' . $acad->agent->last_name;
            $dni = $acad->agent->dni;
            $materia = $acad->materia;
            $vencimiento = $acad->expires_at->format('d/m/Y');
            $linkGestion = url("/padron/{$acad->agent_id}?tab=universidad");

            $body .= "• Agente: {$nombre} (DNI: {$dni})\n";
            $body .= "  Materia: {$materia}\n";
            $body .= "  Vención el: {$vencimiento}\n";
            $body .= "  Gestionar en el panel: {$linkGestion}\n\n";
        }

        $body .= "--------------------------------------------------------\n";
        $body .= "Servicio de Salud Digital — HIGA General San Martín";

        return $body;
    }
}