<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\EventGalleryController;

//auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class,'logout']);

//Event routes
Route::post('/create-event', [EventController::class, 'createEvent']);
Route::get('/get-all-events', [EventController::class, 'fetchEvents']);
Route::get('/get-single-event/{id}', [EventController::class, 'getEvent']);
Route::put('/update-event/{id}', [EventController::class, 'updateEvent']);
Route::delete('/delete-event/{id}', [EventController::class, 'deleteEvent']);

//contact us routes
Route::post('/contact-us', [ContactUsController::class, 'store']);
Route::get('/contact-us', [ContactUsController::class, 'index']);

//Event Gallery 
Route::post('/galleries', [EventGalleryController::class, 'createGallery']);
Route::get('/galleries/{eventId}', [EventGalleryController::class, 'fetchGallery']);
Route::get('/gallery/{id}', [EventGalleryController::class, 'showGallery']);
Route::put('/gallery/{id}', [EventGalleryController::class, 'updateGallery']);
Route::delete('/gallery/{id}', [EventGalleryController::class, 'deleteGallery']);
