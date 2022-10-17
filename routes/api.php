<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\SquareController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AssignCampController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\GeneralController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware(["auth:api", 'localization'])->group(function () {
    Route::prefix('general')->group(function () {
        Route::prefix('types')->group(function () {
            Route::get('/', [TypeController::class, 'index']);
            Route::post('/store', [TypeController::class, 'store']);
            Route::get('/edit/{id}', [TypeController::class, 'edit']);
            Route::post('/update/{id}', [TypeController::class, 'update']);
        });
        Route::prefix('cities')->group(function () {
            Route::get('/', [CityController::class, 'index']);
            Route::get('/edit/{id}', [CityController::class, 'edit']);
            Route::post('/store', [CityController::class, 'store']);
            Route::post('/update/{id}', [CityController::class, 'update']);
        });

        // Route::prefix('squares')->group(function () {
        //     Route::post('/', [SquareController::class, 'index']);
        //     Route::post('/store', [SquareController::class, 'store']);
        //     Route::delete('/delete/{id}', [SquareController::class, 'delete']);
        //     Route::get('/edit/{id}', [SquareController::class, 'edit']);
        //     Route::post('/update/{id}', [SquareController::class, 'update']);
        // });
        Route::prefix('camps')->group(function () {
            Route::post('/', [CampController::class, 'index']);
            Route::get('/', [CampController::class, 'index']);
            Route::get('/get-square', [CampController::class, 'get_square']);
            Route::post('/store', [CampController::class, 'store']);
            Route::delete('/delete/{id}', [CampController::class, 'delete']);
            Route::get('/edit/{id}', [CampController::class, 'edit']);
            Route::post('/update/{id}', [CampController::class, 'update']);
            Route::get('/show-company-ready-camps', [CampController::class, 'showCompanyReadyCamps']);
            Route::get('/camp-by-square/{id}', [CampController::class, 'CampBySquare']);
            Route::post('/updateCampStatus/{id}', [CampController::class, 'updateCampStatus']);
        });
        Route::prefix('squares')->group(function () {
            Route::post('/', [SquareController::class, 'index']);
            Route::post('/store', [SquareController::class, 'store']);
            Route::delete('/delete/{id}', [SquareController::class, 'delete']);
            Route::post('/update/{id}', [SquareController::class, 'update']);
            Route::get('/edit/{id}', [SquareController::class, 'edit']);
        });
        Route::group(['prefix' => 'questions'], function () {
            Route::post('/', [QuestionsController::class, 'index']);
            Route::get('inputs', [QuestionsController::class, 'getInput']);
            Route::post('create', [QuestionsController::class, 'store']);
            Route::get('edit/{id}', [QuestionsController::class, 'edit']);
            Route::post('update/{id}', [QuestionsController::class, 'update']);
            Route::post('store', [QuestionsController::class, 'store']);
            Route::delete('delete/{id}', [QuestionsController::class, 'destroy']);
        });
        Route::group(['prefix' => 'forms'], function () {
            Route::get('/', [FormController::class, 'index']);
            Route::post('update/{id}', [FormController::class, 'update']);
            Route::post('store', [FormController::class, 'store']);
            Route::post('form-answer/{id}', [FormController::class, 'FormAnswer']);
            Route::delete('destroy/{id}', [FormController::class, 'destroy']);
        });
        Route::group(['prefix' => 'contracts'], function () {
            Route::post('/', [ContractController::class, 'index']);
            Route::post('store', [ContractController::class, 'store']);
            Route::get('check-qr/{qr}', [ContractController::class, 'CheckQR']);
            Route::get('view/{id}', [ContractController::class, 'view']);
            Route::get('sign-contract/{id}', [ContractController::class, 'SignContract']);
            Route::delete('destroy/{id}', [ContractController::class, 'destroy']);
        });
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('edit/{id}', [CategoryController::class, 'edit']);
            Route::post('store', [CategoryController::class, 'store']);
            Route::post('update/{id}', [CategoryController::class, 'update']);
            Route::delete('destroy/{id}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('statistics')->group(function () {
            Route::get('dashboard-counter', [GeneralController::class, 'DashboardCounter']);
        });

        //camp assignation
        Route::prefix('camps')->group(function () {
            Route::post('/index', [AssignCampController::class, 'index']);
            Route::post('/assignCampToCompany', [AssignCampController::class, 'assignCampToCompany']);
            Route::post('/updateCampAssignation/{id}', [AssignCampController::class, 'updateCampAssignation']);
            Route::delete('/deleteCampAssignation/{id}', [AssignCampController::class, 'deleteCampAssignation']);
            Route::get('/get-data', [AssignCampController::class, 'getData']);
            Route::get('/editCampAssign/{id}', [AssignCampController::class, 'editCampAssign']);
            Route::get('/editCampByCompany/{id}', [AssignCampController::class, 'editCampByCompany']);
            Route::post('/updateCampByCompany/{id}', [AssignCampController::class, 'updateCampByCompany']);
            Route::get('/fixAssignation', [AssignCampController::class, 'fixAssignation']);
        });

        //appointments
        Route::prefix('appointments')->group(function () {
            Route::post('/', [AppointmentController::class, 'index']);
            Route::post('/appointments', [AppointmentController::class, 'appointments']);
            Route::post('/store', [AppointmentController::class, 'store']);
            Route::post('/update/{id}', [AppointmentController::class, 'update']);
            Route::delete('/delete/{id}', [AppointmentController::class, 'delete']);
        });
    });
});
