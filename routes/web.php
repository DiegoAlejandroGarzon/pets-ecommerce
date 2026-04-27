<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // I will update welcome.blade.php or create a new view
});
