<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'list']);
    Route::get('/{id}', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'create']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
});

Route::get('/status', function () {
    $dbConnected = false;
    $dbError = null;
    $dbName = '';
    
    try {
        DB::connection()->getPdo();
        $dbConnected = true;
        $dbName = DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        $dbError = $e->getMessage();
    }

    $redisConnected = false;
    $redisError = null;

    try {
        Redis::ping();
        $redisConnected = true;
    } catch (\Exception $e) {
        $redisError = $e->getMessage();
    }

    return response()->json([
        'status' => 'online',
        'database' => $dbConnected ? 'connected' : 'disconnected',
        'db_name' => $dbName,
        'database_error' => $dbError,
        'redis' => $redisConnected ? 'connected' : 'disconnected',
        'redis_error' => $redisError,
    ]);
});
