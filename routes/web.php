<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Auth::routes();

Route::get('/', [MeetingController::class, 'index'])->name('home');


Route::middleware(['auth', 'is_admin'])->group(function () {
    
    // Fitur Buat Baru
    Route::get('/buat-meeting', [MeetingController::class, 'create'])->name('create.meeting');
    Route::post('/simpan-meeting', [MeetingController::class, 'store'])->name('simpan.meeting');
    
    // Fitur Hapus
    Route::delete('/meeting/{id}', [MeetingController::class, 'destroy'])->name('hapus.meeting');
    
    // Fitur Edit
    Route::get('/meeting/{id}/edit', [MeetingController::class, 'edit'])->name('edit.meeting');
    Route::put('/meeting/{id}', [MeetingController::class, 'update'])->name('update.meeting');

    // Fitur Tambah User
    Route::get('/tambah-user', [UserController::class, 'create'])->name('create.user');
    Route::post('/simpan-user', [UserController::class, 'store'])->name('store.user');

    // Fitur Tambah Tempat Baru
    Route::get('/tambah-tempat', [App\Http\Controllers\MeetingController::class, 'createPlace'])->name('create.place');
    Route::post('/simpan-tempat', [App\Http\Controllers\MeetingController::class, 'storePlace'])->name('store.place');

});