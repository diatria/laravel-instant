<?php

namespace Diatria\LaravelInstant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ["name", "created_at", "updated_at", "deleted_at"];
}
