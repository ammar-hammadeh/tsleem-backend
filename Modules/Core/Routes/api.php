<?php

use Modules\Core\Entities\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Core\Entities\Permission;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\RoleController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\PermissionController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('password-reset-request', [AuthController::class, 'passwordResetRequest'])->name('password-reset-request');
    Route::post('password-reset', [AuthController::class, 'reset'])->name('password-reset');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/register-data', [AuthController::class, 'register_data']);
    Route::get('/categories/by-type/{type_id}', [CategoryController::class, 'CategoryByType']);
    Route::middleware('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::get('permission-me', [AuthController::class, 'permission_me']);
        Route::get('refresh', [AuthController::class, 'refresh']);
    });
    // Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api', "localization"])->group(function () {
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('permissions-list');
        Route::post('/create', [PermissionController::class, 'create'])->name('permission-create');
        Route::post('/delete/{id}', [PermissionController::class, 'delete'])->name('permission-delete');
        Route::post('/update/{id}', [PermissionController::class, 'update'])->name('permission-update');
        Route::get('/view/{id}', [PermissionController::class, 'view'])->name('permission-view');
        Route::post('/un-assign-role/{id}', [PermissionController::class, 'unAssignRole'])->name('permission-un-assign-role');
    });


    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('role-list');
        Route::get('/get-permissions-users', [RoleController::class, 'get_permissions_users'])->name('role-permissions-users');
        Route::post('/create', [RoleController::class, 'create'])->name('role-create');
        Route::post('/delete/{id}', [RoleController::class, 'delete'])->name('role-delete');
        Route::post('/update/{id}', [RoleController::class, 'update'])->name('role-update');
        Route::get('/view/{id}', [RoleController::class, 'view'])->name('role-view');
    });

    Route::prefix('users')->group(function () {
        Route::post('/', [UserController::class, 'index'])->name('user-list');
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('/create-employee', [UserController::class, 'CreateEmployee'])->name('user-create-employee');
        Route::post('/update', [UserController::class, 'update'])->name('user-update');
        Route::post('/update-roles/{id}', [UserController::class, 'updateRoles'])->name('user-update-roles');
        Route::post('/password/reset/{id}', [UserController::class, 'resetPasswordByAdmin'])->name('user-avatar-delete');
        Route::post('/delete/avatar/{id}', [UserController::class, 'deleteAvatar'])->name('user-avatar-delete');
        Route::get('/data-user', [UserController::class, 'create_data'])->name('create-data');
        Route::post('/switch-user-status/{id}', [UserController::class, 'switchUserStatus'])->name('user-switch-status');
        Route::post('/change-passwored', [UserController::class, 'changePassword']);
        Route::post('/delete/{id}', [UserController::class, 'destroy'])->name('user-delete');
        Route::get('/view-user', [UserController::class, 'viewUser'])->name('view-user');
        Route::get('/view/{id}', [UserController::class, 'view'])->name('view');
        Route::post('/pending-users', [UserController::class, 'PendingUsers'])->name('pending-users');
        Route::post('/show-employees', [UserController::class, 'ShowEmployees'])->name('show-employees');
        Route::post('/create-user', [AuthController::class, 'CreateUser'])->name('create-user');
        Route::post('/update-user-role/{id}', [UserController::class, 'UpdateUserRole'])->name('update-user-role');

        Route::post('/update-me', [UserController::class, 'updateMe']);
        // Route::get('/{id}', [UserController::class, 'view'])->name('user-view');
        // Route::post('/create', [UserController::class, 'create'])->name('user-create');

    });
    // Route::get('temp', function () {

    //     $notificationMessage = __('general.userNeedApprove');
    //     $link = "/users/request/";
    //     (new NotificationController)->addNotification(1, $notificationMessage, $link);

    //     $users = User::permission('user-active')->get();

    //     // 'user-active',
    //     // 'user-rejected',
    // })->name('temp');
});
