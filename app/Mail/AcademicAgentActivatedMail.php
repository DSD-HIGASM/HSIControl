<?php

namespace App\Mail;

use App\Models\AcademicAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcademicAgentActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AcademicAgent $academicProfile;
    public string $activationLink;

    public function __construct(AcademicAgent $academicProfile, string $activationLink)
    {
        $this->academicProfile = $academicProfile;
        $this->activationLink = $activationLink;
    }

    public function build()
    {
        return $this->subject('Activación de Acceso HSI — Convenio UNLP')
                    ->view('emails.academic_agent_activated');
    }
}