<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


use App\Http\Controllers\ControllerOne;
use App\Http\Controllers\ControllerSystem;

use App\Http\Controllers\Api\V1\SystemController;

Route::get('/', function () {
    return view('welcome');
});

