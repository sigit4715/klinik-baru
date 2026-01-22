<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PaginationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::get('/forget-password', [AuthController::class, 'forgetPassword'])->name('forget_password');
    Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/signup', [AuthController::class, 'signup'])->name('signup');

    // Google OAuth Routes
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/lang/{lang}', [LanguageController::class, 'switchLang'])->name('switch_lang');
Route::get('/pagination-per-page/{per_page}', [PaginationController::class, 'set_pagination_per_page'])->name('pagination_per_page');

// Clear cache route (untuk clear cache tanpa SSH)
Route::get('/clear-cache/{key?}', function ($key = null) {
    // Secret key untuk keamanan (ganti dengan key Anda sendiri)
    $secretKey = 'klinik2026';

    if ($key !== $secretKey) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. Gunakan: /clear-cache/' . $secretKey
        ], 401);
    }

    try {
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'Semua cache berhasil dibersihkan!',
            'cleared' => ['config', 'cache', 'view', 'route'],
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal clear cache: ' . $e->getMessage()
        ], 500);
    }
})->name('clear.cache');
