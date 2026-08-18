<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'list']);
        Route::get('/{id}', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'create']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });

    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'list']);
        Route::get('/{id}', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'create']);
        Route::put('/{id}', [ClientController::class, 'update']);
        Route::delete('/{id}', [ClientController::class, 'delete']);
    });

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'list']);
        Route::get('/summary', [ProjectController::class, 'summary']);
        Route::get('/{id}', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'create']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'delete']);

        Route::get('/{id}/transactions', [TransactionController::class, 'index']);
        Route::get('/{id}/transactions/summary', [TransactionController::class, 'summary']);
        Route::post('/{id}/transactions', [TransactionController::class, 'store']);
        Route::delete('/{id}/transactions/{transactionId}', [TransactionController::class, 'destroy']);
    });

    Route::get('/activity-log', [ActivityLogController::class, 'index']);

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
});
