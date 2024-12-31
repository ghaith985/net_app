<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SanctumSettingsController;
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
Route::post('/renew-token', [UserController::class, 'renewToken']);

///////////////////////groups////////////////////////////////
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/creatGroup',[GroupController::class,'creatGroup'])->Middleware('CheckGroupName','PreventAdminActions');
    Route::delete('/deleteGroup',[GroupController::class,'deleteGroup'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::get('/groupUsers',[GroupController::class,'groupUsers']);
    Route::get('/allUserGroup',[GroupController::class,'allUserGroup']);
    Route::post('/addUserToGroup',[GroupController::class,'addUserToGroup'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::post('/deleteUserFromGroup',[GroupController::class,'deleteUserFromGroup','PreventAdminActions']);
    Route::get('/displayAllUser',[UserController::class,'displayAllUser']);
    Route::get('/displayAllGroups',[GroupController::class,'displayAllGroups']);
    Route::get('/searchUser',[GroupController::class,'searchUser','PreventAdminActions']);
    Route::get('/searchGroup',[GroupController::class,'searchGroup','PreventAdminActions']);
    Route::post('/RequestToJoinGroup',[GroupController::class,'RequestToJoinGroup','PreventAdminActions']);
    Route::post('/AcceptedRequest',[GroupController::class,'AcceptedRequest','PreventAdminActions']);
    Route::post('/unAcceptedRequest',[GroupController::class,'unAcceptedRequest','PreventAdminActions']);
    Route::post('/displayUserRequestForGroup',[GroupController::class,'displayUserRequestForGroup']);
    Route::get('/files/{group_id}', [FileController::class, 'getFilesByGroup'])->middleware('CheckMember');
    Route::get('/reserved-files', [FileController::class, 'getReservedFiles'])->middleware('CheckMember');

    ///////////////////////Files////////////////////////////////////////
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/uploadFileToGroup',[FileController::class,'uploadFileToGroup'])->middleware(['PreventAdminActions']);//موافقة من الاونر  //كذا صورة
        Route::post('/downloadFile',[FileController::class,'downloadFile'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
        Route::post('/deleteFile',[FileController::class,'deleteFile'])->middleware(['CheckFileOwner','FileReserved','PreventAdminActions']);
        Route::post('/checkIn',[FileController::class,'checkIn'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
        Route::post('/checkOut',[FileController::class,'checkOut','PreventAdminActions']);
        Route::post('/updateFileAfterCheckOut',[FileController::class,'updateFileAfterCheckOut'])->middleware(['CheckMember','PreventAdminActions']);
        Route::post('/bulkCheckIn',[FileController::class,'bulkCheckIn'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
//        Route::post('/backupFile/{fileId}',[FileController::class,'backupFile']);












    });














});
