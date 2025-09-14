<?php

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
// Laravel expects channel names WITHOUT the 'presence-' prefix here
Broadcast::channel('control-panel', function ($user) {
    if (!Auth::check()) {
        Log::warning('Presence auth denied: unauthenticated');
        return false;
    }
    if (!in_array($user->role, ['admin','editor'])) {
        Log::warning('Presence auth denied: role not allowed', ['user_id' => $user->id, 'role' => $user->role]);
        return false;
    }
    Log::info('Presence auth OK', ['user_id' => $user->id, 'role' => $user->role]);
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});
