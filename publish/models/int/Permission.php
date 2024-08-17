<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        "application_id",
        "name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
