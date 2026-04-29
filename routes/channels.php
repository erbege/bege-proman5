<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // Check if user is part of the project team
    $isTeamMember = $user->projects()->where('projects.id', $projectId)->exists();
    
    // Check if user is the owner (client)
    $isOwner = $user->hasRole('owner'); // Or check if assigned as owner to this project
    
    return $isTeamMember || $isOwner || $user->hasRole('Superadmin');
});
