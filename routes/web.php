<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });



Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/', function () {
        return view('dashboard', [
            'users' => User::all()
        ]);
    })->name('dashboard');

    Route::post('message', [MessageController::class, 'store'])->name('message.store');
    Route::post('messages', [MessageController::class, 'index'])->name('messages.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
