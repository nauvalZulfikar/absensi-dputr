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
use Illuminate\Support\Facades\Artisan;

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

    $router->group(['prefix' => 'attendances'], function ($router) {
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

Route::prefix('user')->middleware('auth:api')->group(function () {
    Route::post('/all', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::post('/selected', [UserController::class, 'userSelectionList']);
    Route::get('summary', [UserController::class, 'summary']);
    Route::post('profile', [UserController::class, 'updateProfile']);
});

Route::prefix('devision')->middleware('auth:api')->group(function () {
    Route::post('/', [DevisionController::class, 'index']);
    Route::post('/store', [DevisionController::class, 'store']);
    Route::get('/show/{id}', [DevisionController::class, 'show']);
    Route::put('/update/{id}', [DevisionController::class, 'update']);
    Route::post('/destroy/{id}', [DevisionController::class, 'destroy']);
    // Route::apiResource('', DevisionController::class);
});

Route::prefix('project')->middleware('auth:api')->group(function () {
    Route::post('/', [ProjectController::class, 'index']);
    Route::post('/store', [ProjectController::class, 'store']);
    Route::get('/show/{id}', [ProjectController::class, 'show']);
    Route::put('/update/{id}', [ProjectController::class, 'update']);
    Route::post('/destroy/{id}', [ProjectController::class, 'destroy']);

    Route::get('/global', [ProjectController::class, 'global_function']);
    Route::get('/detail-project', [ProjectController::class, 'detailProject']);
});

Route::prefix('progres')->middleware('auth:api')->group(function () {
    Route::post('/', [ProgressController::class, 'index']);
    Route::post('/store', [ProgressController::class, 'store']);
    Route::get('/show/{id}', [ProgressController::class, 'show']);
    Route::put('/update/{id}', [ProgressController::class, 'update']);
    Route::post('/destroy/{id}', [ProgressController::class, 'destroy']);
});

Route::prefix('shift')->middleware('auth:api')->group(function () {
    Route::post('/', [ShiftController::class, 'index']);
    Route::post('/store', [ShiftController::class, 'store']);
    Route::get('/show/{id}', [ShiftController::class, 'show']);
    Route::put('/update/{id}', [ShiftController::class, 'update']);
    Route::delete('/destroy/{id}', [ShiftController::class, 'destroy']);
    Route::post('/show2', [ShiftController::class, 'show2']);
    Route::post('/add-user', [ShiftController::class, 'addUserShift']);
    Route::post('/delete-user', [ShiftController::class, 'deleteShiftUser']);
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

Route::prefix('user-project')->middleware(['auth:api'])->group(function () {
    Route::post('/', [UserHaveProjectController::class, 'insertUserAssign']);
    Route::post('/inserts', [UserHaveProjectController::class, 'insertUserAssigns']);
    Route::delete('/{id}', [UserHaveProjectController::class, 'deleteUserAssign']);
});

Route::prefix('user-division')->middleware(['auth:api'])->group(function () {
    Route::post('/', [UserHaveDivisionController::class, 'insertUserAssign']);
    Route::delete('/{id}', [UserHaveDivisionController::class, 'deleteUserAssign']);
});

Route::get('/export', [AttendacesController::class, 'Export']);
Route::get('/export-data/{file_path}', [FileController::class, 'downloadExcel']);

Route::prefix('files')->middleware(['auth:api'])->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::patch('{id}', [FileController::class, 'updateFile']);
});

Route::get('/migrate', function () {
    try {
        // Menjalankan perintah migrate
        Artisan::call('migrate');

        // Jika tanpa error, berikan respons sukses
        return response()->json(['message' => 'Migration successful'], 200);
    } catch (\Exception $e) {
        // Jika terjadi error, berikan respons dengan pesan error
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
