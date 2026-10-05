<?php

namespace App\Models\LaravelInstant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        "name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
