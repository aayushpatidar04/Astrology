<?php

use App\Http\Controllers\Api\AstrologerController;
use App\Http\Controllers\Api\AstroToolController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MainController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('sendOtp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
Route::post('verifyOtp', [AuthController::class, 'verifyOtp'])->middleware('throttle:6,1');
Route::post('/register-astrologer', [AuthController::class, 'registerAstrologer']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('device-token', [AuthController::class, 'storeDeviceToken']);
    Route::post('test-notification', [MainController::class, 'testNotification']);
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
    Route::get('/astrologer/status/{id?}', [App\Http\Controllers\Api\AstrologerController::class, 'getStatus']);
    Route::get('/chogdiya', [MainController::class, 'chogdiya']);
    Route::get('/banners', [MainController::class, 'getBanners']);
    Route::get('/testimonials', [MainController::class, 'getTestimonials']);

    // Tools & AI Astrologer API routes
    Route::post('/tools/kundli', [AstroToolController::class, 'kundli']);
    Route::post('/tools/matchmaking', [AstroToolController::class, 'matchMaking']);
    Route::post('/tools/compatibility', [AstroToolController::class, 'zodiacCompatibility']);
    Route::get('/tools/panchang', [AstroToolController::class, 'panchang']);
    Route::post('/tools/numerology', [AstroToolController::class, 'numerology']);
    Route::post('/tools/baby-names', [AstroToolController::class, 'babyNames']);
    Route::post('/tools/sun-sign', [AstroToolController::class, 'sunSignCalculator']);
    Route::post('/tools/rising-sign', [AstroToolController::class, 'risingSignCalculator']);
    Route::post('/tools/rashi', [AstroToolController::class, 'rashiCalculator']);
    Route::post('/tools/love-calculator', [AstroToolController::class, 'loveCalculator']);
    Route::post('/tools/tarot-reading', [AstroToolController::class, 'tarotReading']);
    Route::post('/tools/atmakaraka-darakaraka', [AstroToolController::class, 'atmakarakaDarakaraka']);
    Route::post('/tools/dasha-calculator', [AstroToolController::class, 'dashaCalculator']);
    Route::post('/tools/nakshatra-calculator', [AstroToolController::class, 'nakshatraCalculator']);
    Route::post('/tools/manglik', [AstroToolController::class, 'manglikCalculator']);
    Route::post('/tools/sade-sati', [AstroToolController::class, 'sadeSatiCalculator']);
    Route::get('/tools/choghadiya', [AstroToolController::class, 'choghadiya']);
    Route::get('/tools/muhurat-finder', [AstroToolController::class, 'muhuratFinder']);
    Route::get('/ai-astrologer/init', [AstroToolController::class, 'aiAstrologerInit']);
    Route::post('/ai-astrologer/send', [AstroToolController::class, 'aiAstrologerSend']);
    Route::post('/ai-astrologer/settle', [AstroToolController::class, 'aiAstrologerSettle']);
    Route::get('/cities/autocomplete', [AstroToolController::class, 'searchCities']);

    Route::middleware('role:User')->group(function () {
        Route::get('/astrologers', [UserController::class, 'astrologers']);
        Route::post('/chats', [UserController::class, 'createChat']);
        Route::post('/chats/{chatId}/start', [UserController::class, 'startChatSession']);
        Route::get('/start-chat/{astrologerId}', [UserController::class, 'startChat']);
        Route::get('/chats', [UserController::class, 'chats']);
        Route::get('/chats/{chatId}/messages', [UserController::class, 'messages']);

        Route::get('/recharge', [UserController::class, 'recharge']);
        Route::get('/wallet', [UserController::class, 'wallet']);
        Route::post('/end-chat/{chatId}', [UserController::class, 'endChat']);
        Route::get('/user/phonepe/webview/{package_id}', [UserController::class, 'phonePeWebView']);
        Route::post('/astrologers/{astrologerId}/reviews', [UserController::class, 'storeReview']);

        Route::post('/call/start', [UserController::class, 'start']);
        Route::post('/calls/{id}/end', [UserController::class, 'callEnd']);
        Route::get('/user/call-history', [UserController::class, 'callHistory']);
        Route::post('/call/signal', [UserController::class, 'signal']);
        Route::post('/call/update-status', [UserController::class, 'updateCallStatus']);
        Route::get('/user/transactions', [UserController::class, 'transactions']);
        Route::patch('/user/profile', [UserController::class, 'updateProfile']);
        Route::delete('/user/profile', [UserController::class, 'destroyProfile']);
    });

    Route::middleware('role:Astrologer')->group(function () {
        Route::get('/astrologer/wallet', [AstrologerController::class, 'wallet']);
        Route::get('/astrologer/chats', [AstrologerController::class, 'chats']);
        Route::get('astrologer/chats/{chatId}/messages', [AstrologerController::class, 'messages']);
        Route::get('/astrologers/{id}/stats', [AstrologerController::class, 'stats']);

        Route::post('/astrologers/{id}/bank-details', [AstrologerController::class, 'storeBankDetails']);
        Route::get('/astrologers/{id}/bank-details', [AstrologerController::class, 'getBankDetails']);

        Route::get('/astrologers/{id}/transactions', [AstrologerController::class, 'getAstrologerTransactions']);

        Route::post('/astrologer/status', [AstrologerController::class, 'status']);
        Route::post('/astrologer/chats/{chatId}/reject', [AstrologerController::class, 'rejectChat']);
        Route::post('/astrologer/chats/{id}/messages', [AstrologerController::class, 'storeMessage']);
        Route::patch('/astrologer/profile', [AstrologerController::class, 'update']);
        Route::post('/astrologer/astrologer', [AstrologerController::class, 'updateAstrologer']);
        Route::get('/astrologer/calls/{id?}', [AstrologerController::class, 'calls']);
        Route::post('/astrologers/{id}/busy', [AstrologerController::class, 'setBusy']);
    });

});
