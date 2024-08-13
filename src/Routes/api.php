<?php
use Illuminate\Support\Facades\Route;
use Diatria\LaravelInstant\Http\Controllers\RoleController;
use Diatria\LaravelInstant\Http\Controllers\UserController;
use Diatria\LaravelInstant\Http\Controllers\PermissionController;
use Diatria\LaravelInstant\Http\Controllers\RolePermissionController;

Route::prefix("api/" . config('laravel-instant.route_prefix'))->group(function () {
    Route::controller(PermissionController::class)->group(function () {
        Route::get("permissions", "all");
        Route::get("permissions/table", "table");
        Route::get("permissions/{id}", "find");
        Route::post("permissions", "create");
        Route::put("permissions/{id}", "update");
        Route::delete("permissions/{id?}", "remove");
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get("roles", "all");
        Route::get("roles/table", "table");
        Route::get("roles/{id}", "find");
        Route::post("roles", "create");
        Route::put("roles/{id}", "update");
        Route::delete("roles/{id?}", "remove");
    });

    Route::controller(RolePermissionController::class)->group(function () {
        Route::get("role-permissions", "all");
        Route::get("role-permissions/table", "table");
        Route::get("role-permissions/{id}", "find");
        Route::post("role-permissions", "create");
        Route::put("role-permissions/{id}", "update");
        Route::delete("role-permissions/{id?}", "remove");
    });

    Route::controller(UserController::class)->group(function () {
        Route::get("users", "all");
        Route::get("users/check-token", "check"); // Check validation token
        Route::get("users/table", "table");
        Route::get("users/{id}", "find");
        Route::post("users", "register");
        Route::post("users/login", "login");
        Route::put("users/{id}", "update");
        Route::delete("users/{id?}", "remove");
    });
});
