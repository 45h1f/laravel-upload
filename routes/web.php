<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::post('upload/media', [\Ashiful\Upload\Http\Controllers\UploadController::class, 'store'])->name('ashiful.upload.store');
    Route::get('upload/signed', [\Ashiful\Upload\Http\Controllers\UploadController::class, 'show'])->name('ashiful.upload.signed');
});

