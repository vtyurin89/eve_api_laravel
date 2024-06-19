<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControllerSafeSystems;
use App\Http\Controllers\ControllerOne;
use App\Http\Controllers\ControllerSystem;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/door_handles', [ControllerSafeSystems::class, 'index']);

Route::get('/one', [ControllerOne::class, 'index']);

Route::get('/system/get', [ControllerSystem::class, 'index']);
Route::get('/system/update', [ControllerSystem::class, 'update']);
Route::get('/system/delete', [ControllerSystem::class, 'delete']);

