<?php

namespace Diatria\LaravelInstant\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ["name", "created_at", "updated_at", "deleted_at"];
}
