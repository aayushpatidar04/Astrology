<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Controller as ApiController;
use App\Http\Controllers\Api\MainController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('sendOtp', [AuthController::class, 'sendOtp']);
Route::post('verifyOtp', [AuthController::class, 'verifyOtp']);
Route::post('/register-astrologer', [AuthController::class, 'registerAstrologer']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('blogs')->group(function () {
        Route::get('/categories', [MainController::class, 'blogCategories']);
        Route::get('/', [MainController::class, 'blogs']);
        Route::get('/category/{id}', [MainController::class, 'blogsByCategory']);
        Route::get('/{id}', [MainController::class, 'showBlog']);
    });

    Route::get('/{type}-horoscopes', [MainController::class, 'horoscopesByType']);
    Route::get('/{type}-horoscope/{sign}', [MainController::class, 'horoscopesByTypeAndSign']);

    Route::post('/chats/{chatId}/messages', [MainController::class, 'sendMessage']);
    Route::post('/chats/{chatId}/typing', [MainController::class, 'typing']);
    Route::get('/chogdiya', [MainController::class, 'chogdiya']);
    Route::get('/banners', [MainController::class, 'getBanners']);
    Route::get('/testimonials', [MainController::class, 'getTestimonials']);


    Route::middleware('role:User')->group(function () {
        Route::get('/astrologers', [UserController::class, 'astrologers']);
        Route::get('/start-chat/{astrologerId}', [UserController::class, 'startChat']);
        Route::get('/chats', [UserController::class, 'chats']);
        Route::get('/chats/{chatId}/messages', [UserController::class, 'messages']);

        Route::get('/recharge', [UserController::class, 'recharge']);

    });

});
