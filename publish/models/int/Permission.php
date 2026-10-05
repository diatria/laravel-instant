<?php

namespace App\Models\LaravelInstant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
