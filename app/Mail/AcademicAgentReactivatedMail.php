<?php

namespace App\Mail;

use App\Models\AcademicAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcademicAgentReactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AcademicAgent $academicProfile;
    public string $reactivationLink;

    public function __construct(AcademicAgent $academicProfile, string $reactivationLink)
    {
        $this->academicProfile = $academicProfile;
        $this->reactivationLink = $reactivationLink;
    }

    public function build()
    {
        $mail = $this->subject('Reactivación de Acceso HSI — UNLP')
                     ->view('emails.academic_agent_reactivated');

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