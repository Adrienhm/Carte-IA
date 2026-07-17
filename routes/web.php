<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CardController as AdminCardController;
use App\Http\Controllers\Admin\CardTypeController;
use App\Http\Controllers\Admin\PackController as AdminPackController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Packs (CDC 4.2 parcours principal)
    Route::get('/packs', [PackController::class, 'index'])->name('packs.index');
    Route::post('/packs/{pack}/open', [PackController::class, 'open'])->name('packs.open');

    // Inventaire (CDC 5.1)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Profils publics et annuaire des joueurs (CDC 5.1)
    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('/players/{user}', [PlayerController::class, 'show'])->name('players.show');

    // Echanges (CDC 4.2 / 5.1)
    Route::get('/trades', [TradeController::class, 'index'])->name('trades.index');
    Route::get('/trades/create/{user}', [TradeController::class, 'create'])->name('trades.create');
    Route::post('/trades', [TradeController::class, 'store'])->name('trades.store');
    Route::get('/trades/{trade}', [TradeController::class, 'show'])->name('trades.show');
    Route::post('/trades/{trade}/accept', [TradeController::class, 'accept'])->name('trades.accept');
    Route::post('/trades/{trade}/reject', [TradeController::class, 'reject'])->name('trades.reject');
    Route::post('/trades/{trade}/cancel', [TradeController::class, 'cancel'])->name('trades.cancel');

    // Compte utilisateur (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Back-office administrateur (CDC 4.3)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::post('cards/generate', [AdminCardController::class, 'generate'])->name('cards.generate');
    Route::resource('cards', AdminCardController::class)->except('show');
    Route::resource('card-types', CardTypeController::class)->except('show');
    Route::resource('packs', AdminPackController::class);

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
    Route::delete('users/{user}/cards/{userCard}', [AdminUserController::class, 'destroyCard'])->name('users.cards.destroy');
    Route::post('users/{user}/grant-pack', [AdminUserController::class, 'grantPack'])->name('users.grant-pack');
});

require __DIR__.'/auth.php';
