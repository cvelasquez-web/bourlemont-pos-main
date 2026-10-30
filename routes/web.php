<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ── Pública ──────────────────────────────────────────────
Route::view('/', 'index')->name('login');

// ── Protegidas por sesión web ────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::view('/pos',        'pos')->name('pos');
    Route::view('/inventario', 'inventario')->name('inventario');
    Route::view('/recetas',    'recetas')->name('recetas');
    Route::view('/auditoria',  'auditoria')->name('auditoria');
    Route::view('/usuarios',   'usuarios')->name('usuarios');
    Route::view('/dashboard',  'dashboard')->name('dashboard');

    // Logout (invalida sesión)
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});