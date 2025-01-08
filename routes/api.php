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
    Route::post('/deleteUserFromGroup',[GroupController::class,'deleteUserFromGroup'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::get('/displayAllUser',[UserController::class,'displayAllUser'])->middleware('PreventAdminActions');
    Route::get('/displayAllGroups',[GroupController::class,'displayAllGroups'])->middleware('PreventAdminActions');
    Route::get('/searchUser',[GroupController::class,'searchUser'])->middleware('PreventAdminActions');
    Route::get('/searchGroup',[GroupController::class,'searchGroup'])->middleware('PreventAdminActions');
    Route::post('/RequestToJoinGroup',[GroupController::class,'RequestToJoinGroup'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::post('/AcceptedRequest',[GroupController::class,'AcceptedRequest'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::post('/unAcceptedRequest',[GroupController::class,'unAcceptedRequest'])->middleware('CheckGroupOwner','PreventAdminActions');
    Route::post('/displayUserRequestForGroup',[GroupController::class,'displayUserRequestForGroup'])->middleware('PreventAdminActions');
    Route::get('/files/{group_id}', [FileController::class, 'getFilesByGroup'])->middleware('CheckMember');
    Route::get('/reserved-files', [FileController::class, 'getReservedFiles'])->middleware('CheckMember');

    ///////////////////////Files////////////////////////////////////////
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/uploadFileToGroup',[FileController::class,'uploadFileToGroup'])->middleware(['PreventAdminActions']);//موافقة من الاونر  //كذا صورة
        Route::post('/downloadFile',[FileController::class,'downloadFile'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
        Route::post('/deleteFile',[FileController::class,'deleteFile'])->middleware(['CheckFileOwner','FileReserved','PreventAdminActions']);
        Route::post('/checkIn',[FileController::class,'checkIn'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
        Route::post('/checkOut',[FileController::class,'checkOut'])->middleware('CheckMember','PreventAdminActions');
        Route::post('/updateFileAfterCheckOut',[FileController::class,'updateFileAfterCheckOut'])->middleware(['CheckMember','PreventAdminActions','fileTracing']);
        Route::post('/bulkCheckIn',[FileController::class,'bulkCheckIn'])->middleware(['CheckMember','FileReserved','PreventAdminActions']);
//        Route::post('/backupFile/{fileId}',[FileController::class,'backupFile']);
        Route::get('/showFileReport/{file_id}', [FileController::class, 'showFileReport'])->middleware('CheckMember');
        Route::get('/showFileReportPdf/{file_id}', [FileController::class, 'showFileReportPdf'])->middleware('CheckMember');
        Route::get('/showFileReportCsv/{file_id}', [FileController::class, 'showFileReportCsv'])->middleware('CheckMember');













    });














});
