<?php

namespace Diatria\LaravelInstant\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class QueryMaker
{
    /** @var Model|null */
    protected $model;

    /** @var array */
    protected $queries = [];

    /** @var array */
    protected $columns = [];

    /** @var array */
    protected $relations = [];

    /** @var array */
    protected $relationsCount = [];

    /** @var bool */
    protected $pagination = false;

    /** @var int */
    protected $paginationLength = GeneralConfig::PAGINATE_PER_PAGE;

    /** @var int|null */
    protected $limit;

    /** @var string|null */
    protected $order;

    /** @var string|null */
    protected $mode;

    /** @var bool */
    protected $authentication = false;

    /** Initialize the query definition. */
    public function initial(Collection $request)
    {
        $this->reset();
        $this->model = $request->get('model');
        $this->queries = (array) $request->get('queries', []);
        $this->columns = (array) $request->get('columns', []);
        $this->limit = $request->get('limit');
        $this->order = $request->get('order', 'created_at:asc');
        $this->pagination = (bool) $request->get('pagination', false);
        $this->paginationLength = (int) $request->get(
            'pagination_length',
            GeneralConfig::PAGINATE_PER_PAGE
        );
        $this->mode = $request->get('mode');
        $this->authentication = (bool) $request->get(
            'authentication',
            $request->get('auth', false)
        );

        return $this;
    }

    public function setPagination($pagination)
    {
        $this->paginationLength = max(1, (int) $pagination);
        $this->pagination = true;

        return $this;
    }

    public function unsetPagination()
    {
        $this->pagination = false;

        return $this;
    }

    public function setRelations($relations)
    {
        $this->relations = $this->normalizeList($relations);

        return $this;
    }

    public function setRelationsCount($relations)
    {
        $this->relationsCount = $this->normalizeList($relations);

        return $this;
    }

    /** @return mixed */
    public function create()
    {
        try {
            return $this->execute($this->buildQuery());
        } catch (ErrorException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ErrorException($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /** @return Builder */
    protected function buildQuery()
    {
        if (!$this->model instanceof Model) {
            throw new ErrorException(
                "Model not found, please initiate it first, use 'initModel()'",
                404
            );
        }

        $query = $this->model->newQuery();
        $query = $this->applyAuthentication($query);
        $query = $this->applyFilters($query);
        $query = $this->applyRelations($query);
        $query = $this->applyColumns($query);

        return $this->applyOrder($query);
    }

    /** @param Builder $query */
    protected function applyAuthentication(Builder $query)
    {
        if ($this->authentication) {
            $query->where('user_id', Helper::getUserID());
        }

        return $query;
    }

    /** @param Builder $query */
    protected function applyFilters(Builder $query)
    {
        foreach ($this->queries as $definition) {
            $filter = collect($definition);
            $field = $filter->get('field');
            $this->assertAllowedField($field);
            $query = $this->applyFilter($query, $field, $filter);
        }

        return $query;
    }

    /** @param Builder $query */
    protected function applyFilter(Builder $query, $field, Collection $filter)
    {
        $operator = strtolower((string) $filter->get('op', ''));
        $value = $filter->get('value');

        if ($operator === '') {
            return $this->applyDefaultFilter($query, $field, $value, $filter);
        }

        switch ($operator) {
            case 'eq':
                return $query->where($field, '=', $value);
            case 'ne':
                return $query->where($field, '!=', $value);
            case 'gt':
                return $query->where($field, '>', $value);
            case 'gte':
                return $query->where($field, '>=', $value);
            case 'lt':
                return $query->where($field, '<', $value);
            case 'lte':
                return $query->where($field, '<=', $value);
            case 'like':
                return $query->where($field, 'LIKE', "%{$value}%");
            case 'not_like':
                return $query->where($field, 'NOT LIKE', "%{$value}%");
            case 'begins_with':
                return $query->where($field, 'LIKE', "{$value}%");
            case 'ends_with':
                return $query->where($field, 'LIKE', "%{$value}");
            case 'in':
                return $query->whereIn($field, $this->asList($value));
            case 'not_in':
                return $query->whereNotIn($field, $this->asList($value));
            case 'between':
                return $query->whereBetween($field, $this->asRange($value));
            case 'not_between':
                return $query->whereNotBetween($field, $this->asRange($value));
            case 'null':
                return $query->whereNull($field);
            case 'not_null':
                return $query->whereNotNull($field);
            default:
                throw new ErrorException("Unsupported query operator: {$operator}", 422);
        }
    }

    /** @param Builder $query */
    protected function applyDefaultFilter(Builder $query, $field, $value, Collection $filter)
    {
        if (is_array($value) || $value instanceof Collection) {
            return $query->whereIn($field, $this->asList($value));
        }

        if ((bool) $filter->get('strict', false)) {
            return $query->where($field, '=', $value);
        }

        return $query->where($field, 'LIKE', "%{$value}%");
    }

    protected function asList($value)
    {
        $list = $value instanceof Collection ? $value->all() : (array) $value;

        if (!$list) {
            throw new ErrorException('Operator requires a non-empty list', 422);
        }

        return array_values($list);
    }

    protected function asRange($value)
    {
        $range = $this->asList($value);
        if (count($range) !== 2) {
            throw new ErrorException('Between operator requires exactly two values', 422);
        }

        return $range;
    }

    /** @param Builder $query */
    protected function applyRelations(Builder $query)
    {
        if ($this->relations) {
            $query->with($this->relations);
        }

        if ($this->relationsCount) {
            $query->withCount($this->relationsCount);
        }

        return $query;
    }

    /** @param Builder $query */
    protected function applyColumns(Builder $query)
    {
        if ($this->columns) {
            $columns = array_unique(array_merge($this->columns, [$this->model->getKeyName()]));
            $query->select($columns);
        }

        return $query;
    }

    /** @param Builder $query */
    protected function applyOrder(Builder $query)
    {
        if (!$this->order) {
            return $query;
        }

        list($field, $direction) = array_pad(explode(':', $this->order, 2), 2, 'asc');
        $direction = strtolower($direction ?: 'asc');
        $this->assertAllowedField($field);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new ErrorException('Invalid order direction', 422);
        }

        return $query->orderBy($field, $direction);
    }

    /** @param Builder $query */
    protected function execute(Builder $query)
    {
        if ($this->pagination) {
            return $query->paginate(max(1, $this->paginationLength));
        }

        if ($this->limit !== null) {
            return $query->limit(max(0, (int) $this->limit))->get();
        }

        if ($this->mode === 'first') {
            return $query->first();
        }

        return $query->get();
    }

    protected function assertAllowedField($field)
    {
        if (!config('laravel-instant.query.reject_unknown_fields', true)) {
            return;
        }

        $allowed = array_merge(
            (array) $this->model->getFillable(),
            [$this->model->getKeyName(), 'created_at', 'updated_at', 'deleted_at']
        );

        if (!$field || !in_array($field, array_unique($allowed), true)) {
            throw new ErrorException('Invalid query field', 422);
        }
    }

    protected function normalizeList($value)
    {
        if ($value instanceof Collection) {
            return $value->values()->all();
        }

        return array_values(array_filter((array) $value));
    }

    protected function reset()
    {
        $this->model = null;
        $this->queries = [];
        $this->columns = [];
        $this->relations = [];
        $this->relationsCount = [];
        $this->pagination = false;
        $this->paginationLength = GeneralConfig::PAGINATE_PER_PAGE;
        $this->limit = null;
        $this->order = null;
        $this->mode = null;
        $this->authentication = false;
    }
}
