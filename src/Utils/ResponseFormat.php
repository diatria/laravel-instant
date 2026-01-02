<?php

namespace Diatria\LaravelInstant\Utils;

use Diatria\LaravelInstant\Traits\InstantServiceTrait;

class ResponseFormat
{
    use InstantServiceTrait;

    protected $field, $fieldArray, $fieldTable;

    protected $tablePath = "/";

    protected $appends = [];

    protected $relations = [];

    public function append(array $appends)
    {
        try {
            $this->appends = [...$this->appends, ...$appends];
            return $this;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function array(array $fieldArray)
    {
        try {
            $this->fieldArray = $fieldArray;
            return $this->formatingArray();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function object($fieldObject)
    {
        try {
            $this->field = Helper::toObject($fieldObject);
            return $this->formatingObject();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function table(object $filedTable)
    {
        try {
            $this->fieldTable = $filedTable;
            return $this->formatingTable();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function format(object $field) {}

    public function formatingObject()
    {
        try {
            if (!$this->field) {
                return null;
            }

            return (object) $this->format(Helper::toObject($this->field));
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function formatingArray()
    {
        try {
            $data = [];

            foreach ($this->fieldArray as $field) {
                $data[] = $this->format(Helper::toObject($field));
            }

            return $data;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function formatingTable()
    {
        try {
            $this->fieldArray = $this->fieldTable;
            return (new TableMaker())->reCreate($this->fieldTable, $this->formatingArray(), [
                "path" => $this->tablePath,
            ]);
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function with(array $relation)
    {
        try {
            $this->relations = $relation;
            return $this;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }
}
