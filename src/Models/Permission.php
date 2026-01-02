<?php

namespace Diatria\LaravelInstant\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        "application_id",
        "name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
