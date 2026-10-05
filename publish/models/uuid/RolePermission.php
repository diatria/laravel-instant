<?php

namespace App\Models\LaravelInstant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RolePermission extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        "role_id",
        "permission_id",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    public function Role()
    {
        return $this->belongsTo(Role::class);
    }

    public function Permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
