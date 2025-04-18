<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;

//auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Event routes
Route::post('/create-event', [EventController::class, 'createEvent']);
Route::get('/get-all-events', [EventController::class, 'fetchEvents']);
Route::get('/get-single-envent/{id}', [EventController::class, 'getEvent']);
Route::put('/update-event/{id}', [EventController::class, 'updateEvent']);
Route::delete('/delete-event/{id}', [EventController::class, 'deleteEvent']);