<!DOCTYPE html>
<html>
<head>
    <title>Team Invitation</title>
</head>
<body>
    <h1>You've been invited to join {{ $company->name }}</h1>
    
    <p>{{ $invitation->invitedBy->name }} has invited you to join {{ $company->name }} on our CRM platform.</p>
    
    <p><strong>Role:</strong> {{ ucfirst($invitation->role) }}</p>
    
    <p><strong>Company:</strong> {{ $company->name }}</p>
    
    <p>This invitation will expire on {{ $invitation->expires_at->format('M d, Y') }}.</p>
    
    <p>
        <a href="{{ $acceptUrl }}" style="background-color: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Accept Invitation
        </a>
    </p>
    
    <p>If you don't want to accept this invitation, you can safely ignore this email.</p>
</body>
</html>