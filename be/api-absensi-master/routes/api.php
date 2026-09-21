<?php

use App\Http\Controllers\AttendacesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DevisionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserHaveDivisionController;
use App\Http\Controllers\UserHaveProjectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    // 'prefix' => 'auth'
], function ($router) {
    $router->group(['prefix' => 'auth'], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Legacy alias of POST /api/attendance (store). Dulu unauth + percaya userId
    // dari body → siapa pun bisa palsu-in kehadiran orang lain. Sekarang wajib login;
    // userId diambil dari token, bukan request. Duplikat store() — kandidat dihapus.
    $router->group(['prefix' => 'attendances', 'middleware' => 'auth:api'], function ($router) {
        Route::post('/', [AttendacesController::class, 'Attendances']);
    });

    $router->group(['prefix' => 'project'], function ($router) {
        // Route::get('/', [ProjectController::class, 'index']);
        // Route::post('/store', [ProjectController::class, 'store']);
        // Route::get('/show/{id}', [ProjectController::class, 'show']);
        // Route::post('/update/{id}', [ProjectController::class, 'update']);
        // Route::post('/destroy/{id}', [ProjectController::class, 'destroy']);

        // Route::get('/global', [ProjectController::class, 'global_function']);
        // Route::get('/detail-project', [ProjectController::class, 'detailProject']);
    });

    // $router->group(['prefix' => 'devision'], function ($router) {
    //     Route::get('/', [DevisionController::class, 'index']);
    //     Route::post('/store', [DevisionController::class, 'store']);
    //     Route::get('/show/{id}', [DevisionController::class, 'show']);
    //     Route::post('/update/{id}', [DevisionController::class, 'update']);
    //     Route::post('/destroy/{id}', [DevisionController::class, 'destroy']);
    //     // Route::apiResource('', DevisionController::class);
    // })->middleware(['auth:api']);
});

// Gate admin: role admin/user_admin. Route self-service (profile sendiri,
// list/show yang dipakai user biasa) tetap auth:api aja.
$adminOnly = 'role:admin,user_admin';

Route::prefix('user')->middleware('auth:api')->group(function () use ($adminOnly) {
    Route::post('/all', [UserController::class, 'index'])->middleware($adminOnly);
    Route::post('/', [UserController::class, 'store'])->middleware($adminOnly);
    Route::post('/selected', [UserController::class, 'userSelectionList'])->middleware($adminOnly);
    Route::get('summary', [UserController::class, 'summary'])->middleware($adminOnly);
    Route::post('profile', [UserController::class, 'updateProfile']); // ganti profil/password sendiri
});

Route::prefix('devision')->middleware('auth:api')->group(function () use ($adminOnly) {
    Route::post('/', [DevisionController::class, 'index']);
    Route::get('/show/{id}', [DevisionController::class, 'show']);
    Route::post('/store', [DevisionController::class, 'store'])->middleware($adminOnly);
    Route::put('/update/{id}', [DevisionController::class, 'update'])->middleware($adminOnly);
    Route::post('/destroy/{id}', [DevisionController::class, 'destroy'])->middleware($adminOnly);
});

Route::prefix('project')->middleware('auth:api')->group(function () use ($adminOnly) {
    Route::post('/', [ProjectController::class, 'index']);
    Route::get('/show/{id}', [ProjectController::class, 'show']);
    Route::get('/global', [ProjectController::class, 'global_function']);
    Route::get('/detail-project', [ProjectController::class, 'detailProject']);
    Route::post('/store', [ProjectController::class, 'store'])->middleware($adminOnly);
    Route::put('/update/{id}', [ProjectController::class, 'update'])->middleware($adminOnly);
    Route::post('/destroy/{id}', [ProjectController::class, 'destroy'])->middleware($adminOnly);
});

Route::prefix('progres')->middleware('auth:api')->group(function () use ($adminOnly) {
    Route::post('/', [ProgressController::class, 'index']);
    Route::get('/show/{id}', [ProgressController::class, 'show']);
    Route::post('/store', [ProgressController::class, 'store'])->middleware($adminOnly);
    Route::put('/update/{id}', [ProgressController::class, 'update'])->middleware($adminOnly);
    Route::post('/destroy/{id}', [ProgressController::class, 'destroy'])->middleware($adminOnly);
});

Route::prefix('shift')->middleware('auth:api')->group(function () use ($adminOnly) {
    Route::post('/', [ShiftController::class, 'index']);
    Route::get('/show/{id}', [ShiftController::class, 'show']);
    Route::post('/show2', [ShiftController::class, 'show2']);
    Route::post('/store', [ShiftController::class, 'store'])->middleware($adminOnly);
    Route::put('/update/{id}', [ShiftController::class, 'update'])->middleware($adminOnly);
    Route::delete('/destroy/{id}', [ShiftController::class, 'destroy'])->middleware($adminOnly);
    Route::post('/add-user', [ShiftController::class, 'addUserShift'])->middleware($adminOnly);
    Route::post('/delete-user', [ShiftController::class, 'deleteShiftUser'])->middleware($adminOnly);
});

Route::prefix('attendance')->middleware('auth:api')->group(function () {
    Route::get('/', [AttendacesController::class, 'index']);
    Route::post('/', [AttendacesController::class, 'store']);
    Route::get('/summary', [AttendacesController::class, 'summary']);

    Route::get('/log', [AttendacesController::class, 'attendanceLogs']);
});

Route::prefix("media")->middleware('auth:api')->group(function () {
    Route::post('/', [MediaController::class, 'store']);
});

Route::prefix("profile")->middleware(['auth:api'])->group(function () {
    Route::get('/', [AuthController::class, 'userProfile']);
    Route::get('/me', [ProfileController::class, 'me']);
    Route::post('edit', [ProfileController::class, 'update']);
    Route::post('update/{id}', [ProfileController::class, 'update']);
});

Route::prefix('roles')->middleware(['auth:api'])->group(function () {
    Route::get('/', [RoleController::class, 'index']);
});

Route::prefix('user-project')->middleware(['auth:api', 'role:admin,user_admin'])->group(function () {
    Route::post('/', [UserHaveProjectController::class, 'insertUserAssign']);
    Route::post('/inserts', [UserHaveProjectController::class, 'insertUserAssigns']);
    Route::delete('/{id}', [UserHaveProjectController::class, 'deleteUserAssign']);
});

Route::prefix('user-division')->middleware(['auth:api', 'role:admin,user_admin'])->group(function () {
    Route::post('/', [UserHaveDivisionController::class, 'insertUserAssign']);
    Route::delete('/{id}', [UserHaveDivisionController::class, 'deleteUserAssign']);
});

Route::middleware(['auth:api', 'role:admin,user_admin'])->group(function () {
    Route::get('/export', [AttendacesController::class, 'Export']);
    // Batasi nama file ke satu segmen aman — blokir path-traversal (../, %2F)
    Route::get('/export-data/{file_path}', [FileController::class, 'downloadExcel'])
        ->where('file_path', '[A-Za-z0-9_\-.]+');
});

Route::prefix('files')->middleware(['auth:api', 'role:admin,user_admin'])->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::patch('{id}', [FileController::class, 'updateFile']);
});
