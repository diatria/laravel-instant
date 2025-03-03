<?php

use Illuminate\Support\Facades\Route;
use Diatria\LaravelInstant\Http\Controllers\RoleController;
use Diatria\LaravelInstant\Http\Controllers\UserController;
use Diatria\LaravelInstant\Http\Controllers\PermissionController;
use Diatria\LaravelInstant\Http\Controllers\RolePermissionController;

Route::prefix("api/" . config('laravel-instant.route.prefix'))->group(function () {
    Route::get("permissions", [PermissionController::class, "all"]);
    Route::get("permissions/table", [PermissionController::class, "table"]);
    Route::get("permissions/{id}", [PermissionController::class, "find"]);
    Route::post("permissions", [PermissionController::class, "create"]);
    Route::put("permissions/{id}", [PermissionController::class, "update"]);
    Route::delete("permissions/{id?}", [PermissionController::class, "remove"]);

    Route::get("roles", [RoleController::class, "all"]);
    Route::get("roles/table", [RoleController::class, "table"]);
    Route::get("roles/{id}", [RoleController::class, "find"]);
    Route::post("roles", [RoleController::class, "create"]);
    Route::put("roles/{id}", [RoleController::class, "update"]);
    Route::delete("roles/{id?}", [RoleController::class, "remove"]);

    Route::get("role-permissions", [RolePermissionController::class, "all"]);
    Route::get("role-permissions/table", [RolePermissionController::class, "table"]);
    Route::get("role-permissions/{id}", [RolePermissionController::class, "find"]);
    Route::post("role-permissions", [RolePermissionController::class, "create"]);
    Route::put("role-permissions/{id}", [RolePermissionController::class, "update"]);
    Route::delete("role-permissions/{id?}", [RolePermissionController::class, "remove"]);

    Route::get("users", [RolePermissionController::class, "all"]);
    Route::get("users/check", [RolePermissionController::class, "check"]); // Check validation token
    Route::get("users/table", [RolePermissionController::class, "table"]);
    Route::get("users/{id}", [RolePermissionController::class, "find"]);
    Route::post("users", [RolePermissionController::class, "register"]);
    Route::post("users/login", [RolePermissionController::class, "login"]);
    Route::post("users/token/refresh", [RolePermissionController::class, "refreshToken"]);
    Route::put("users/{id}", [RolePermissionController::class, "update"]);
    Route::delete("users/{id?}", [RolePermissionController::class, "remove"]);
});
