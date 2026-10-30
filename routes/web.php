<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/usuarios', function () {
    return view('usuarios');
})->name('usuarios');

Route::get('/auditoria', function () {
    return view('auditoria');
})->name('auditoria');

