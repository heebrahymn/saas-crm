<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitation;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:invitations,email',
            'role' => 'required|in:admin,manager,staff',
        ]);

        $company = $request->attributes->get('tenant');
        $invitingUser = $request->user();

        // Only admins can invite
        if ($invitingUser->role !== 'admin') {
            return response()->json([
                'message' => 'Only administrators can invite team members'
            ], 403);
        }

        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $invitingUser->id,
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7), // 7-day expiry
        ]);

        // Send invitation email
        Mail::to($request->email)->send(new TeamInvitation($invitation));

        return response()->json([
            'message' => 'Invitation sent successfully',
            'invitation' => $invitation
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Invalid invitation token'
            ], 404);
        }

        if ($invitation->isExpired()) {
            return response()->json([
                'message' => 'Invitation has expired'
            ], 400);
        }

        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'Invitation already accepted'
            ], 400);
        }

        return response()->json([
            'invitation' => $invitation,
            'company' => $invitation->company
        ]);
    }

    public function complete(Request $request, string $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|confirmed|min:8',
        ]);

        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Invalid invitation token'
            ], 404);
        }

        if ($invitation->isExpired()) {
            return response()->json([
                'message' => 'Invitation has expired'
            ], 400);
        }

        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'Invitation already accepted'
            ], 400);
        }

        // Create user
        $user = User::create([
            'company_id' => $invitation->company_id,
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => bcrypt($request->password),
        ]);

        // Assign role
        UserRole::create([
            'user_id' => $user->id,
            'company_id' => $invitation->company_id,
            'role' => $invitation->role,
        ]);

        // Mark invitation as accepted
        $invitation->update([
            'accepted_at' => now()
        ]);

        // Login the user
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Welcome to the team!',
            'user' => $user,
            'token' => $token,
            'company' => $user->company,
            'role' => $user->role,
            'redirect_url' => $this->getTenantUrl($user->company->subdomain)
        ]);
    }

    private function getTenantUrl(string $subdomain): string
    {
        $suffix = config('app.tenant_subdomain_suffix', '.app.test');
        return "https://{$subdomain}{$suffix}";
    }
}