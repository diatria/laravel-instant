<?php
namespace Diatria\LaravelInstant\Utils;

use App\Models\User;

class Permission
{
    protected $permission, $action;
    public function __construct($permission)
    {
        $this->permission = $permission;
    }

    public function can($action)
    {
        return true;
        $this->action = $action;

        $tokenInfo = Token::info();
        $user = User::where("uuid", $tokenInfo["uuid"])
            ->first()
            ->append("permissions");

        $haveAccess = in_array(
            $this->getAction(),
            $user->permissions->toArray()
        );

        if (!$haveAccess) {
            throw new ErrorException("Permission denied", 403);
        }

        return $haveAccess;
    }
    public function getAction()
    {
        return Helper::get($this->permission, $this->action);
    }
}
