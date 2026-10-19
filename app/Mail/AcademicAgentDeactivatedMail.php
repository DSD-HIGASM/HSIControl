<?php

namespace App\Mail;

use App\Models\AcademicAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcademicAgentDeactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AcademicAgent $academicProfile;

    public function __construct(AcademicAgent $academicProfile)
    {
        $this->academicProfile = $academicProfile;
    }

    public function build()
    {
        $mail = $this->subject('Desactivación de Cuenta HSI — UNLP')
                     ->view('emails.academic_agent_deactivated');

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