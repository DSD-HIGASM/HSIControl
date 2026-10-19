<?php

namespace App\Mail;

use App\Models\AcademicAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcademicAgentExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public AcademicAgent $academicProfile;

    public function __construct(AcademicAgent $academicProfile)
    {
        $this->academicProfile = $academicProfile;
    }

    public function build()
    {
        $mail = $this->subject('Próximo Vencimiento de Acceso HSI — UNLP')
                     ->view('emails.academic_agent_expiring');

        $filePath = public_path('forms/Anexo_I_solicitud_usuario_UNLP.pdf');
        if (file_exists($filePath)) {
            $mail->attach($filePath, [
                'as' => 'Anexo_I_solicitud_usuario_UNLP.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}