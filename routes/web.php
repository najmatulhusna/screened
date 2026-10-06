<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WatchItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Collection
Route::get('/', [WatchItemController::class, 'index'])->name('collection');
Route::get('/watchitem/{id}', [WatchItemController::class, 'detail'])->name('watchitem.detail');
Route::post('/watchitem/{id}/review', [WatchItemController::class, 'storeReview'])->name('watchitem.review');

// Auth User
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User
Route::get('/statistics', [WatchItemController::class, 'statistics'])->name('statistics')->middleware('auth');
Route::post('/watchitem/{id}/favorite', [WatchItemController::class, 'toggleFavorite'])->name('watchitem.favorite');
Route::get('/favorites', [WatchItemController::class, 'favorites'])->name('favorites')->middleware('auth');
Route::delete('/favorites/{id}', [WatchItemController::class, 'destroyFavorite'])->name('favorites.destroy')->middleware('auth');
Route::delete('/watchitem/{id}/progress', [WatchItemController::class, 'destroyProgress'])->name('watchitem.progress.destroy')->middleware('auth');

// Auth Admin
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
Route::get('/admin/register', [AuthController::class, 'showAdminRegister'])->name('admin.register');
Route::post('/admin/register', [AuthController::class, 'adminRegister']);

// Admin Panel (protected by admin middleware)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/manage', [AdminController::class, 'manage'])->name('manage');
    Route::get('/api/items', [AdminController::class, 'apiIndex'])->name('api.items');
    Route::delete('/review/{id}', [AdminController::class, 'destroyReview'])->name('review.destroy');
    Route::get('/add', [AdminController::class, 'add'])->name('add');
    Route::post('/add', [AdminController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('edit');
    Route::put('/edit/{id}', [AdminController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('destroy');
});