<?php

namespace App\Utils;

use App\Traits\InstantServiceTrait;
use Illuminate\Support\Collection;

class ResponseFormat
{
    use InstantServiceTrait;

    protected $field, $fieldArray, $fieldTable;

    protected $tablePath = "/";

    public function array(array $fieldArray)
    {
        $this->fieldArray = $fieldArray;
        return $this->formatingArray();
    }

    public function object(object|null $fieldObject)
    {
        $this->field = $fieldObject;
        return $this->formatingObject();
    }

    public function table(object $filedTable)
    {
        $this->fieldTable = $filedTable;
        return $this->formatingTable();
    }

    public function format(object $field)
    {
    }

    public function formatingObject()
    {
        if (!$this->field) {
            return null;
        }

        return $this->format($this->field);
    }

    public function formatingArray()
    {
        $data = [];

        foreach ($this->fieldArray as $field) {
            $data[] = $this->format(Helper::toObject($field));
        }

        return $data;
    }

    public function formatingTable()
    {
        $this->fieldArray = $this->fieldTable;
        return (new TableMaker())->reCreate(
            $this->fieldTable,
            $this->formatingArray(),
            [
                "path" => $this->tablePath,
            ]
        );
    }
}
