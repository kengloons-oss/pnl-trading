<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradeImportController;
use App\Http\Controllers\TradingAccountController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/markets', MarketController::class)->name('markets.index');
    Route::get('/accounts', [TradingAccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [TradingAccountController::class, 'store'])->name('accounts.store');
    Route::get('/imports', [TradeImportController::class, 'create'])->name('imports.create');
    Route::post('/imports', [TradeImportController::class, 'upload'])->name('imports.upload');
    Route::get('/imports/preview', [TradeImportController::class, 'preview'])->name('imports.preview');
    Route::post('/imports/confirm', [TradeImportController::class, 'confirm'])->name('imports.confirm');
    Route::resource('trades', TradeController::class)->except(['show']);
});
