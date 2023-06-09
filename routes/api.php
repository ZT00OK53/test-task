<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\UserController;
use App\Http\Controllers\api\v1\UsermanagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Open Routes
Route::post('v1/register', [UserController::class, 'create']);
Route::post('v1/login', [UserController::class, 'login']);

Route::group(['middleware' => 'throttle:20,1', 'prefix'=>'v1/user'], function(){
    Route::get('all', [UsermanagementController::class, 'index']);
    Route::get('/{id}', [UsermanagementController::class, 'detail']);
    // User Management
    Route::group(['middleware' => ['auth:api']], function(){
        Route::get('/', [UserController::class, 'profile']);    
        Route::post('create', [UsermanagementController::class, 'create']);
        Route::post('update/{id}', [UsermanagementController::class, 'update']);
        Route::get('enable/{id}', [UsermanagementController::class, 'restore']);
        Route::delete('disable/{id}', [UsermanagementController::class, 'destroy']);
    });
});