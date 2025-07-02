<?php

use App\Events\JobCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// routes/web.php
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

Route::get('/test-broadcast', function () {
    broadcast(new JobCompleted('Test job completed!', 1));
    return 'Event broadcasted!';
});

Route::post('/broadcasting/auth', function () {
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $socketId = request()->input('socket_id');
    $channelName = request()->input('channel_name');

    \Log::info('Broadcasting auth request', [
        'user_id' => $user->id,
        'socket_id' => $socketId,
        'channel_name' => $channelName
    ]);

    // Handle both channel name formats
    $allowedChannels = [
        "private-user.{$user->id}",
        "private-App.Models.User.{$user->id}"
    ];

    if (in_array($channelName, $allowedChannels)) {
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true
            ]
        );

        $auth = $pusher->socket_auth($channelName, $socketId);

        //\Log::info('Broadcasting auth success', ['auth' => $auth]);

        return response($auth);
    }

    \Log::error('Broadcasting auth failed - channel mismatch', [
        'requested_channel' => $channelName,
        'allowed_channels' => $allowedChannels
    ]);
    return response()->json(['error' => 'Forbidden'], 403);
})->middleware('auth');

