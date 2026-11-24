<?php

use Illuminate\Support\Facades\Route;

// Página principal: o front (public/index.html). style.css e script.js saem direto da pasta public.
Route::get('/', fn () => response()->file(public_path('index.html')));
