<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
