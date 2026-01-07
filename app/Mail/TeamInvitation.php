<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function build()
    {
        return $this->subject('You\'ve been invited to join ' . $this->invitation->company->name)
                    ->view('emails.team-invitation')
                    ->with([
                        'invitation' => $this->invitation,
                        'company' => $this->invitation->company,
                        'acceptUrl' => $this->getAcceptUrl($this->invitation->token),
                    ]);
    }

    private function getAcceptUrl(string $token): string
    {
        $suffix = config('app.tenant_subdomain_suffix', '.app.test');
        return "https://app{$suffix}/accept-invitation/{$token}";
    }
}