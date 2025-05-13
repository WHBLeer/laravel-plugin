<?php

use Illuminate\Support\Facades\Route;
use Sanlilin\LaravelPlugin\Http\Controllers\LaravelPluginController;
Route::middleware(['auth:admin'])->prefix('admin')->as('admin.')->group(function () {
	Route::get('/plugin',[LaravelPluginController::class,'index'])->name('plugin');

	Route::prefix('plugin')->as('plugin.')->group (function () {
		Route::get('/market',[LaravelPluginController::class,'market'])->name('market');
		Route::post('/disable',[LaravelPluginController::class,'disable'])->name('disable');
		Route::post('/enable',[LaravelPluginController::class,'enable'])->name('enable');
		Route::post('/restart',[LaravelPluginController::class,'restart'])->name('restart');
		Route::post('/delete',[LaravelPluginController::class,'delete'])->name('delete');
		Route::any('/local',[LaravelPluginController::class,'local'])->name('local');
		Route::any('/pack_up',[LaravelPluginController::class,'pack_up'])->name('pack_up');
		Route::any('/install',[LaravelPluginController::class,'install'])->name('install');
		Route::any('/publish',[LaravelPluginController::class,'publish'])->name('publish');
		Route::any('/register',[LaravelPluginController::class,'register'])->name('register');
		Route::any('/login',[LaravelPluginController::class,'login'])->name('login');
		Route::any('/upload',[LaravelPluginController::class,'upload'])->name('upload');
		Route::any('/download',[LaravelPluginController::class,'download'])->name('download');
		Route::post('/batch',[LaravelPluginController::class,'batch'])->name('batch');
	});
});
