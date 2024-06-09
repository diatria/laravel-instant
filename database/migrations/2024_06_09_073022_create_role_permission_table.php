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
            $table->id();
            $table->string("name");
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();
            $table->bigInteger("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("roles", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();
            $table->bigInteger("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("role_permissions", function (Blueprint $table) {
            $table->id();
            $table->bigInteger("role_id");
            $table->bigInteger("permission_id");
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();
            $table->bigInteger("deleted_by")->nullable();
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
