<?php

use App\Events\JobCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-broadcast', function () {
    $userId = auth()->id(); // Or use a test ID
    broadcast(new JobCompleted("Test broadcast for user $userId", $userId));
    return 'Event dispatched!';
});

Route::post('/broadcasting/auth', function (Request $request) {
//    Log::info('Broadcasting auth request', [
//        'user' => auth()->user(),
//        'channel_name' => $request->channel_name,
//    ]);

    $response = Broadcast::auth($request);
    Log::info('Broadcasting auth Request', ['request' => $request]);
    return $response;
})->middleware(['web', 'auth']);
//Broadcast::routes(['middleware' => ['web', 'auth']]);
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
