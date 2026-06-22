<?php

use App\Http\Controllers\Admin\PluginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
    Route::post('plugins/{name}/activate', [PluginController::class, 'activate'])->name('plugins.activate');
    Route::post('plugins/{name}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
});
