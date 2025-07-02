<?php
// routes/channels.php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// Handle the simple format: user.{id}
Broadcast::channel('user.{id}', function ($user, $id) {
    Log::info('🔍 Simple channel authorization', [
        'user_exists' => $user !== null,
        'user_id' => $user ? $user->id : null,
        'channel_id' => $id,
    ]);

    if (!$user) {
        return false;
    }

    return (int) $user->id === (int) $id;
});

// Handle the model format: App.Models.User.{id}
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::info('🔍 Model channel authorization', [
        'user_exists' => $user !== null,
        'user_id' => $user ? $user->id : null,
        'channel_id' => $id,
    ]);

    if (!$user) {
        return false;
    }

    return (int) $user->id === (int) $id;
});
