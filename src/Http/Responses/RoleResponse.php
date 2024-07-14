<?php
namespace Diatria\LaravelInstant\Http\Responses;

use Diatria\LaravelInstant\Utils\ResponseFormat;

class RoleResponse extends ResponseFormat
{
    public $tablePath = "roles/table";

    public function format(object $field)
    {
        return [
            "id" => $field->id,
            "name" => $field->name,
        ];
    }
}
