<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\SquareController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\AssignCampController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\ElectricalMeterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EngineerOfficeCategoryController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\QuestionCategoryController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\TentController;
use App\Http\Controllers\WashroomController;
use App\Http\Controllers\WatertapeController;
use App\Http\Controllers\ZoneController;
use App\Models\QuestionCategory;
use App\Models\SystemLog;

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
            Route::get('signer-type', [TypeController::class, 'signerType']);
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
            Route::get('/camps', [CampController::class, 'camps']);
            Route::get('/get-square', [CampController::class, 'get_square']);
            Route::get('/get-all-data', [CampController::class, 'getData']);
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
            Route::post('/forms', [FormController::class, 'forms']);
            Route::get('/forms', [FormController::class, 'forms']);
            Route::get('/questions/{id}', [FormController::class, 'questionsByFrom']);
            Route::post('/form-with-answerd-q', [FormController::class, 'FormWithAnswerdQ']);
            Route::get('/get-data', [FormController::class, 'get_data']);
            Route::get('/edit/{id}', [FormController::class, 'edit']);
            Route::post('update/{id}', [FormController::class, 'update']);
            Route::post('store', [FormController::class, 'store']);
            Route::post('sign-form', [FormController::class, 'SignForm']);
            Route::post('allotment-need-sign', [FormController::class, 'AllotmentNeedSign']);
            Route::post('form-details', [FormController::class, 'formDetails']);
            Route::post('form-update-answer', [FormController::class, 'FormUpdateAnswer']);
            Route::post('questions-with-answer-ids', [FormController::class, 'QuestionsWithAnswerIds']);
            Route::post('upload-answer-attach', [FormController::class, 'UploadAnswerAttach']);
            Route::post('send-notification', [FormController::class, 'SendNotification']);
            Route::delete('destroy/{id}', [FormController::class, 'destroy']);
            Route::delete('delete-answer-attach/{id}', [FormController::class, 'DeleteAnswerAttach']);
        });
        Route::group(['prefix' => 'contracts'], function () {
            Route::post('/', [ContractController::class, 'index']);
            Route::post('store', [ContractController::class, 'store']);
            Route::get('check-qr/{qr}', [ContractController::class, 'CheckQR']);
            Route::get('view/{id}', [ContractController::class, 'view']);
            Route::get('view-by-code/{code}', [ContractController::class, 'viewByCode']);
            Route::get('sign-contract/{id}', [ContractController::class, 'SignContract']);
            Route::post('bulk-sign', [ContractController::class, 'BulkSign']);
            Route::post('bulk-store', [ContractController::class, 'bulkStore']);
            Route::delete('destroy/{id}', [ContractController::class, 'destroy']);
        });
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/get-data', [CategoryController::class, 'get_data']);
            Route::get('/by-type/{type_id}', [CategoryController::class, 'CategoryByType']);
            Route::get('edit/{id}', [CategoryController::class, 'edit']);
            Route::post('store', [CategoryController::class, 'store']);
            Route::post('update/{id}', [CategoryController::class, 'update']);
            Route::delete('destroy/{id}', [CategoryController::class, 'destroy']);
        });

        Route::group(['prefix' => 'engineer-office'], function () {
            Route::get('/', [EngineerOfficeCategoryController::class, 'index']);
            Route::get('edit/{id}', [EngineerOfficeCategoryController::class, 'edit']);
            Route::post('store', [EngineerOfficeCategoryController::class, 'store']);
            Route::post('update/{id}', [EngineerOfficeCategoryController::class, 'update']);
            Route::delete('destroy/{id}', [EngineerOfficeCategoryController::class, 'destroy']);
        });

        Route::prefix('statistics')->group(function () {
            Route::get('dashboard-counter', [GeneralController::class, 'DashboardCounter']);
            Route::get('dashboard-chart', [GeneralController::class, 'DashboardChart']);
            Route::post('filter-user-chart', [GeneralController::class, 'FilterUserChart']);
            Route::post('filter-assign-camp-chart', [GeneralController::class, 'FilterAssignCampChart']);
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
        Route::prefix('companies')->group(function () {
            Route::get('raft-companies', [CompanyController::class, 'RaftCompany']);
            Route::post('update', [CompanyController::class, 'update']);
        });

        // notifications
        Route::group(['prefix' => 'notification'], function () {
            Route::post('/user_notification', [NotificationController::class, 'userNotification']);
            Route::get('/make_notification_seen/{id}', [NotificationController::class, 'makeNotificationSeen']);
            Route::get('/make_all_notification_seen', [NotificationController::class, 'makeAllNotificationSeen']);
        });

        //Question Category
        Route::group(['prefix' => 'question_category'], function () {
            Route::post('/', [QuestionCategoryController::class, 'index']);
            Route::get('/getbyid/{id}', [QuestionCategoryController::class, 'getCategoryByID']);
            Route::post('store', [QuestionCategoryController::class, 'store']);
            Route::post('update/{id}', [QuestionCategoryController::class, 'update']);
            Route::delete('destroy/{id}', [QuestionCategoryController::class, 'destroy']);
            Route::get('/get-data', [QuestionCategoryController::class, 'get_data']);
            Route::get('/get_questions', [QuestionCategoryController::class, 'getQuestions']);
        });

        Route::group(['prefix' => 'logs'], function () {
            Route::post('/', [AuditLogController::class, 'index']);
        });
        Route::group(['prefix' => 'system_logs'], function () {
            Route::post('/', [SystemLogController::class, 'index']);
        });
    });

    Route::group(['prefix' => 'gis'], function () {
        Route::group(['prefix' => 'map'], function () {
            Route::get('/', [GisController::class, 'index']);
            Route::get('check-token', [GisController::class, 'CheckToken']);
        });
        Route::group(['prefix' => 'establishments'], function () {
            Route::get('/', [EstablishmentController::class, 'index']);
            Route::get('/get-data', [EstablishmentController::class, 'get_data']);
            Route::post('store', [EstablishmentController::class, 'store']);
            Route::post('store-with-plots', [EstablishmentController::class, 'storeWithPlots']);
            Route::get('edit/{id}', [EstablishmentController::class, 'edit']);
            Route::post('update/{id}', [EstablishmentController::class, 'updateWithPlots']);
            Route::post('update-with-plots/{id}', [EstablishmentController::class, 'update']);
            Route::delete('destroy/{id}', [EstablishmentController::class, 'destroy']);
        });
        Route::group(['prefix' => 'kitchens'], function () {
            Route::get('/', [KitchenController::class, 'index']);
            Route::get('/get-data', [KitchenController::class, 'get_data']);
            Route::post('store', [KitchenController::class, 'store']);
            Route::post('store-with-camps', [KitchenController::class, 'storeWitCamps']);
            Route::get('/edit/{id}', [KitchenController::class, 'edit']);
            Route::post('update/{id}', [KitchenController::class, 'update']);
            Route::post('update-with-camps/{id}', [KitchenController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [KitchenController::class, 'destroy']);
        });
        Route::group(['prefix' => 'tents'], function () {
            Route::get('/', [TentController::class, 'index']);
            Route::get('/get-data', [TentController::class, 'get_data']);
            Route::post('store', [TentController::class, 'store']);
            Route::post('store-with-camps', [TentController::class, 'storeWitCamps']);
            Route::get('edit/{id}', [TentController::class, 'edit']);
            Route::post('update/{id}', [TentController::class, 'update']);
            Route::post('update-with-camps/{id}', [TentController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [TentController::class, 'destroy']);
        });
        Route::group(['prefix' => 'plots'], function () {
            Route::get('/', [PlotController::class, 'index']);
            Route::get('/get-data', [PlotController::class, 'get_data']);
            Route::post('store', [PlotController::class, 'store']);
            Route::post('store-with-establishments', [PlotController::class, 'storeWithEstablishments']);
            Route::get('/edit/{id}', [PlotController::class, 'edit']);
            Route::post('update/{id}', [PlotController::class, 'update']);
            Route::post('update-with-establishments/{id}', [PlotController::class, 'updateWithEstablishments']);
            Route::delete('destroy/{id}', [PlotController::class, 'destroy']);
        });
        Route::group(['prefix' => 'zones'], function () {
            Route::get('/', [ZoneController::class, 'index']);
            Route::post('store', [ZoneController::class, 'store']);
            // Route::post('update/{id}', [ZoneController::class, 'update']);
            Route::delete('destroy/{id}', [ZoneController::class, 'destroy']);
        });
        Route::group(['prefix' => 'locations'], function () {
            Route::get('/', [LocationController::class, 'index']);
            Route::post('store', [LocationController::class, 'store']);
            Route::get('edit/{id}', [LocationController::class, 'edit']);
            Route::post('update/{id}', [LocationController::class, 'update']);
            Route::delete('destroy/{id}', [LocationController::class, 'destroy']);
        });
        Route::group(['prefix' => 'washrooms'], function () {
            Route::get('/', [WashroomController::class, 'index']);
            Route::get('/get-data', [WashroomController::class, 'get_data']);
            Route::post('store', [WashroomController::class, 'store']);
            Route::post('store-with-camps', [WashroomController::class, 'storeWitCamps']);
            Route::get('/edit/{id}', [WashroomController::class, 'edit']);
            Route::post('update/{id}', [WashroomController::class, 'update']);
            Route::post('update-with-camps/{id}', [WashroomController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [WashroomController::class, 'destroy']);
        });
        Route::group(['prefix' => 'containers'], function () {
            Route::get('/', [ContainerController::class, 'index']);
            Route::get('/get-data', [ContainerController::class, 'get_data']);
            Route::post('store', [ContainerController::class, 'store']);
            Route::post('store-with-camps', [ContainerController::class, 'storeWitCamps']);
            Route::get('edit/{id}', [ContainerController::class, 'edit']);
            Route::post('update/{id}', [ContainerController::class, 'update']);
            Route::post('update-with-camps/{id}', [ContainerController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [ContainerController::class, 'destroy']);
        });
        Route::group(['prefix' => 'electrical_meters'], function () {
            Route::get('/', [ElectricalMeterController::class, 'index']);
            Route::get('/get-data', [ElectricalMeterController::class, 'get_data']);
            Route::post('store', [ElectricalMeterController::class, 'store']);
            Route::post('store-with-camps', [ElectricalMeterController::class, 'storeWitCamps']);
            Route::get('/edit/{id}', [ElectricalMeterController::class, 'edit']);
            Route::post('update/{id}', [ElectricalMeterController::class, 'update']);
            Route::post('update-with-camps/{id}', [ElectricalMeterController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [ElectricalMeterController::class, 'destroy']);
        });
        Route::group(['prefix' => 'water-tape'], function () {
            Route::get('/', [WatertapeController::class, 'index']);
            Route::get('/get-data', [WatertapeController::class, 'get_data']);
            Route::post('store', [WatertapeController::class, 'store']);
            Route::post('store-with-camps', [WatertapeController::class, 'storeWitCamps']);
            Route::get('edit/{id}', [WatertapeController::class, 'edit']);
            Route::post('update/{id}', [WatertapeController::class, 'update']);
            Route::post('update-with-camps/{id}', [WatertapeController::class, 'updateWithCamps']);
            Route::delete('destroy/{id}', [WatertapeController::class, 'destroy']);
        });
    });
});
Route::get("test-sms", [GeneralController::class, 'TestSMS']);
Route::get("otp-sms", [GeneralController::class, 'OTPSMS']);
Route::get("verfiy-sms", [GeneralController::class, 'VerfiySMS']);
Route::get("test-email", [GeneralController::class, 'TestEmail']);
