<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("permissions", function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name");
            $table->uuid("created_by")->nullable();
            $table->uuid("updated_by")->nullable();
            $table->uuid("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("roles", function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name");
            $table->string("code");
            $table->uuid("created_by")->nullable();
            $table->uuid("updated_by")->nullable();
            $table->uuid("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("role_permissions", function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("role_id");
            $table->uuid("permission_id");
            $table->uuid("created_by")->nullable();
            $table->uuid("updated_by")->nullable();
            $table->uuid("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("permissions");
        Schema::dropIfExists("roles");
        Schema::dropIfExists("role_permissions");
    }
};
