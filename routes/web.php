<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WatchItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\PaymentController;

// Collection
Route::get('/', [WatchItemController::class, 'index'])->name('collection');
Route::get('/watchitem/{id}', [WatchItemController::class, 'detail'])->name('watchitem.detail');
Route::post('/watchitem/{id}/review', [WatchItemController::class, 'storeReview'])->name('watchitem.review');

// Streaming (wajib login; akses VIP/preview diatur di dalam controller)
Route::get('/watch/{id}/{ep?}', [WatchController::class, 'show'])
    ->whereNumber('id')->whereNumber('ep')
    ->middleware('auth')
    ->name('watch');

// Payment (Midtrans)
Route::get('/vip', [PaymentController::class, 'vipPage'])->name('vip');
Route::post('/checkout', [PaymentController::class, 'checkout'])->middleware('auth')->name('checkout');
Route::get('/payment/status/{orderId}', [PaymentController::class, 'status'])->middleware('auth')->name('payment.status');
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/done', [PaymentController::class, 'done'])->name('payment.done');

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

// Admin Panel (protected by admin middleware)
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\VipPackageController;

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

    // Transaksi & pengaturan VIP (pengaturan digabung ke halaman ini)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/api/transactions', [TransactionController::class, 'apiIndex'])->name('api.transactions');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Kelola paket VIP (harga + durasi)
    Route::post('/vip-packages', [VipPackageController::class, 'store'])->name('packages.store');
    Route::delete('/vip-packages/{id}', [VipPackageController::class, 'destroy'])->name('packages.destroy');
});

Route::get('/_tg-debug', fn () => [
    'review' => config('services.telegram.thread_review'),
    'error' => config('services.telegram.thread_error'),
    'token_set' => (bool) config('services.telegram.token'),
]);