<?php
namespace Diatria\LaravelInstant\Http\Responses;

use Diatria\LaravelInstant\Utils\ResponseFormat;

class PermissionResponse extends ResponseFormat
{
    public $tablePath = "permissions/table";

    public function format(object $field)
    {
        return [
            "id" => $field->id,
            "name" => $field->name,
        ];
    }
}
