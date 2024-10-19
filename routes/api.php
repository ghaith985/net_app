<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
///////////////////////user////////////////////////////////
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::post('/logout',[UserController::class,'logout']);
///////////////////////groups////////////////////////////////
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/creatGroup',[GroupController::class,'creatGroup']);
    Route::delete('/deleteGroup',[GroupController::class,'deleteGroup']);
    Route::get('/groupUsers',[GroupController::class,'groupUsers']);
    Route::get('/allUserGroup',[GroupController::class,'allUserGroup']);
    Route::post('/addUserToGroup',[GroupController::class,'addUserToGroup']);
    Route::post('/deleteUserFromGroup',[GroupController::class,'deleteUserFromGroup']);
//    Route::post('/userDeleteFromGroup',[GroupController::class,'userDeleteFromGroup']);



});
