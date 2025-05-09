<?php

use Illuminate\Support\Facades\Route;
use Sanlilin\LaravelPlugin\Http\Controllers\LaravelPluginController;

Route::middleware(['auth:admin'])->as('admin.')->prefix('admin')->group(function () {
	Route::as('plugin.')->prefix('plugin')->group(function () {
		Route::get('/',[LaravelPluginController::class,'index']);
		Route::get('/market',[LaravelPluginController::class,'market'])->name('market');
		Route::post('/disable',[LaravelPluginController::class,'disable'])->name('disable');
		Route::post('/enable',[LaravelPluginController::class,'enable'])->name('enable');
		Route::post('/delete',[LaravelPluginController::class,'delete'])->name('delete');
		Route::post('/batch',[LaravelPluginController::class,'batch'])->name('batch');
		Route::any('/setting',[LaravelPluginController::class,'setting'])->name('setting');
		Route::any('/install',[LaravelPluginController::class,'install'])->name('install');
		Route::any('/local',[LaravelPluginController::class,'local'])->name('local');
		Route::any('/publish',[LaravelPluginController::class,'publish'])->name('publish');
		Route::any('/register',[LaravelPluginController::class,'register'])->name('register');
		Route::any('/login',[LaravelPluginController::class,'login'])->name('login');
		Route::any('/upload',[LaravelPluginController::class,'upload'])->name('upload');
		Route::any('/download',[LaravelPluginController::class,'download'])->name('download');
	});
});