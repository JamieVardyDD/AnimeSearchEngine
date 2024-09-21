<?php

use App\Http\Controllers\AnimeSearchController;
use App\Http\Controllers\ImageSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route to display the page (GET)
Route::get('/search-image', function () {
    return view('search'); // Corresponds to resources/views/search.blade.php
})->name('image.search.view');

// Route to handle the search request (POST)
Route::post('/search-image', [ImageSearchController::class, 'search'])->name('image.search');
Route::post('/search-anime', [AnimeSearchController::class, 'searchAnime'])->name('anime.search');
Route::get('/search-anime', [AnimeSearchController::class, 'searchAnime']);


