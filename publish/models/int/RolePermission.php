<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

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
