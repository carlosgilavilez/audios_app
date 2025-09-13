<?php

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Example private channel for a user model. Adjust as needed for your app.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) ($user->id ?? 0) === (int) $id;
});

// Presence channel for admin/editor online presence in admin area
Broadcast::channel('presence.control-panel', function ($user) {
    if (!Auth::check()) {
        return false;
    }
    // Allow only admins and editors
    if (!in_array($user->role, ['admin','editor'])) {
        return false;
    }
    // The data returned is available to channel members
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});
