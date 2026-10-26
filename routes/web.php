<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Vitalis PharmaSys
|--------------------------------------------------------------------------
| Portal Público, Portal Privado (Sistema) y Autenticación Split-Screen.
*/

// Portal Público
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Portal Privado (Panel del Sistema con Lanzador 9 Puntos)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Autenticación (Bocetos 4 y 5)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');
