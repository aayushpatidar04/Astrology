<?php

use App\Events\CallSignal;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AstrologerController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebRTCController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [MainController::class, 'index'])->name('index');
Route::get('/about-us', [MainController::class, 'about'])->name('about-us');
Route::get('/services', [MainController::class, 'services'])->name('services');
Route::get('/blog', [MainController::class, 'blog'])->name('blog');
Route::get('/blog/category/{slug}', [MainController::class, 'categoryBlogs'])->name('category-blog');
Route::get('/blog/{slug}', [MainController::class, 'blogDetails'])->name('blog-details');
Route::get('/horoscope/{type}-horoscope/{sign}', [MainController::class, 'horoscope'])->name('horoscope');
Route::get('/horoscope/{type}-horoscope', [MainController::class, 'horoscopeType'])->name('horoscope-type');

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/blogs', [AdminController::class, 'blogs'])->name('blogs');

    Route::post('/blog-category', [AdminController::class, 'blogCategoryStore'])->name('blog-category.store');
    Route::post('/blogs', [AdminController::class, 'blogStore'])->name('blogs.store');
    Route::post('/blogs/{id}', [AdminController::class, 'blogUpdate'])->name('blogs.update');
    Route::delete('/blogs/{id}', [AdminController::class, 'blogDelete'])->name('blogs.delete');

    Route::get('/admin/horoscopes', [AdminController::class, 'horoscopes'])->name('admin.horoscopes');
    Route::post('/admin/horoscopes', [AdminController::class, 'storeHoroscope'])->name('admin.horoscopes.store');

    Route::get('/admin/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::post('/admin/banners', [AdminController::class, 'bannerStore'])->name('admin.banners.store');
    Route::post('/admin/banners/{id}', [AdminController::class, 'updateBanner'])->name('admin.banners.edit');
    Route::delete('/admin/banners/{id}', [AdminController::class, 'deleteBanner'])->name('admin.banners.destroy');
    Route::patch('/admin/banners/{id}', [AdminController::class, 'activeBanner'])->name('admin.banners.toggle');

    Route::get('/astrologers', [AdminController::class, 'astrologers'])->name('admin.astrologers');
    Route::post('/astrologers/{astrologer}/status', [AdminController::class, 'updateStatus'])->name('admin.astrologers.updateStatus');
    Route::post('/astrologers/{astrologer}/pricing', [AdminController::class, 'updatePricing'])->name('admin.astrologers.updatePricing');

    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
});

Route::middleware(['auth', 'verified', 'role:Astrologer'])->group(function () {
    Route::get('/astrologer/dashboard', [AstrologerController::class, 'dashboard'])->name('astrologer.dashboard');
    Route::post('/astrologer/status', [AstrologerController::class, 'status'])->name('astrologer.status');
    Route::get('/astrologer/chats/{id?}', [AstrologerController::class, 'chats'])->middleware('astrologer.status')->name('astrologer.chats');
    Route::post('/astrologer/chats/{id}/messages', [AstrologerController::class, 'storeMessage'])->name('astrologer.chats.storeMessage');

    Route::get('/astrologer/profile', [AstrologerController::class, 'edit'])->name('astrologer.profile.edit');
    Route::patch('/astrologer/profile', [AstrologerController::class, 'update'])->name('astrologer.profile.update');
    Route::post('/astrologer/astrologer', [AstrologerController::class, 'updateAstrologer'])->name('astrologer.astrologer.update');
    Route::delete('/astrologer/profile', [AstrologerController::class, 'destroy'])->name('astrologer.profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:User'])->group(function () {
    Route::get('/user/chat-with-astrologers', [UserController::class, 'chatWithAstrologers'])->name('user.chat-with-astrologers');
    Route::get('/user/chat/start/{astrologer}', [UserController::class, 'startChat'])->name('user.chat.start');
    Route::get('/user/chats/{id}', action: [UserController::class, 'showChat'])->name('user.chat.show');
    Route::post('/user/chats/{id}/message', [UserController::class, 'storeMessage'])->name('user.chat.storeMessage');
    Route::get('/user/chat-sessions', [UserController::class, 'chatSessions'])->name('user.chat.sessions');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/chat/{chat}/typing', [ProfileController::class, 'typing'])->name('chat.typing');

    Route::post('/call/start', [ProfileController::class, 'start'])->name('call');
    Route::post('/call/signal', [ProfileController::class, 'signal'])->name('signal');
    Route::post('/user/chats/{id}/end', [ProfileController::class, 'chatEnd'])->name('user.chat.end');

    Route::post('/beams-auth', [ProfileController::class, 'beamAuth'])->name('beam.auth');
});


require __DIR__ . '/auth.php';
