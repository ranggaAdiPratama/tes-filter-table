<?php

use App\Http\Controllers\SinglePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SinglePageController::class, 'index']);
