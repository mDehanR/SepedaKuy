<?php

use App\Http\Controllers\Admin\AdminBikeController;
use App\Http\Controllers\Admin\AdminRentalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserRentalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('user.dashboard');
})->name('dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminRentalController::class, 'dashboard'])->name('dashboard');

        Route::resource('bikes', AdminBikeController::class)->except(['show']);

        Route::get('/rentals', [AdminRentalController::class, 'index'])->name('rentals.index');
        Route::post('/rentals/{rental}/approve', [AdminRentalController::class, 'approve'])->name('rentals.approve');
        Route::post('/rentals/{rental}/reject', [AdminRentalController::class, 'reject'])->name('rentals.reject');
        Route::post('/rentals/{rental}/return', [AdminRentalController::class, 'returnBike'])->name('rentals.return');
        Route::post('/rentals/{rental}/mark-fine-paid', [AdminRentalController::class, 'markFinePaid'])->name('rentals.mark-fine-paid');
    });

Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [UserRentalController::class, 'index'])->name('dashboard');
        Route::get('/rentals/create', [UserRentalController::class, 'quickCreate'])->name('rentals.quick-create');
        Route::post('/rentals', [UserRentalController::class, 'quickStore'])->name('rentals.quick-store');
        Route::get('/bikes/{bike}/rent', [UserRentalController::class, 'create'])->name('rentals.create');
        Route::post('/bikes/{bike}/rent', [UserRentalController::class, 'store'])->name('rentals.store');
        Route::get('/my-rentals', [UserRentalController::class, 'myRentals'])->name('rentals.index');
        Route::get('/my-rentals/{rental}/edit', [UserRentalController::class, 'edit'])->name('rentals.edit');
        Route::put('/my-rentals/{rental}', [UserRentalController::class, 'update'])->name('rentals.update');
        Route::post('/rentals/{rental}/pay-fine', [UserRentalController::class, 'payFine'])->name('rentals.pay-fine');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
