<?php

namespace Diatria\LaravelInstant\Utils;

use Illuminate\Support\Collection;

class QueryMaker
{
    /**
     * Class model yang digunakan
     *
     * @var class
     */
    protected $model;

    /**
     * Query / conditional untuk pengambilan data
     * Dalam tiap tiap list array terdapat 3 kolom yaitu `field`, `value`, `strict`
     * - field	(string)	: kolom yang dicari pada database
     * - value	(string)	: value yang dicari pada database
     * - strict (boolean)	: menampilkan hasil yang sama persis atau tidak
     *
     * @var array
     */
    protected $queries = [];

    /**
     * Menapilkan kolom terpilih
     * Jika kosong akan menampilkan semua kolom
     *
     * @var array
     */
    protected $columns = [];

    /**
     * List relasi
     *
     * @var array
     */
    protected $relations;

    /**
     * List relasi untuk mendapatkan jumlah data
     *
     * @var array
     */
    protected $relationsCount;

    /**
     * Menampilkan hasil dalam bentuk pagination atau raw
     *
     * @var bool
     */
    protected $pagination = true;

    /**
     * Jumlah data yang akan ditampilkan dalam 1 halaman
     *
     * @var int
     */
    protected $paginationLength = GeneralConfig::PAGINATE_PER_PAGE;

    /**
     * Jumlah data yang akan ditampilkan
     *
     * @var int
     */
    protected $limit;

    /**
     * Urutan kolom yang akan ditampilkan
     *
     * @var string
     */
    protected $order;

    /**
     * Menampilkan data satuan atau multiple
     * `many` = menampilkan banyak data
     * `one` = menampilkan hanya satu data
     *
     * @var string
     */
    protected $mode;

    /**
     * Menampilkan data yang dimiliki user terauthentikasi
     * @var boolean
     */
    protected $authentication;

    /**
     * Inisiasi variable
     * - model				required	Class
     * - queries			optional	Array
     * - columns			optional	Array
     * - pagination			optional	Boolean
     * - pagination_length	optional	Number
     * - mode				optional	Enum('first', 'get')
     */
    public function initial(Collection $request)
    {
        // Initial variable
        $this->model = $request->get('model');
        $this->queries = $request->get('queries', []);
        $this->columns = $request->get('columns', []);
        $this->limit = $request->get('limit');
        $this->order = $request->get('order', 'created_at:asc');
        $this->pagination = $request->get('pagination', false);
        $this->paginationLength = $request->get('pagination_length', GeneralConfig::PAGINATE_PER_PAGE);
        $this->mode = $request->get('mode');
        $this->authentication = $request->get('auth');

        return $this;
    }

    /**
     * Set jumlah list data yang akan tampil dalam 1 halaman
     * @param int $pagination
     */
    public function setPagination($pagination)
    {
        $this->paginationLength = $pagination;
        return $this;
    }

    /**
     * Menghilangkan pagination dan menampilkan dalam bentuk raw
     */
    public function unsetPagination()
    {
        $this->pagination = false;
        return $this;
    }

    /**
     * Mengatur list relasi
     * @param array $relations
     */
    public function setRelations($relations)
    {
        if ($relations) {
            $this->relations = $relations;
        }
        return $this;
    }

    public function setRelationsCount($relations)
    {
        if ($relations) {
            $this->relationsCount = $relations;
        }
        return $this;
    }

    /**
     * Membuat query
     *
     */
    public function create()
    {
        try {
            $query = $this->model;
            if (!$query) {
                throw new ErrorException("Model not found, please initiate it first, use 'initModel()'", 404);
            }
            if ($this->queries) {
                foreach ($this->queries as $item) {
                    $item = collect($item);
                    if ($item->get('strict')) {
                        $query = $query->where($item->get('field'), $item->get('value'));
                    } elseif (gettype($item->get('value')) === 'object') {
                        $query = $query->whereIn($item->get('field'), $item->get('value'));
                    } else {
                        $value = $item->get('value');
                        $query = $query->where($item->get('field'), 'like', "%{$value}%");
                    }
                }
            }

            // Relation
            $query = $query->when($this->relations, function ($query, $relationsQuery) {
                $query->with($relationsQuery);
            });

            // Relation Count
            $query = $query->when($this->relationsCount, function ($query, $relationsQuery) {
                $query->withCount($relationsQuery);
            });

            if ($this->columns) {
                $query = $query->select(
                    collect($this->columns)
                        ->push('id')
                        ->toArray(),
                );
            }
            if ($this->order) {
                $splitText = explode(':', $this->order); // Contoh text: 'name:asc'
                $order = [
                    'field' => $splitText[0] ?? 'created_at',
                    'mode' => $splitText[1] ?? 'asc',
                ];
                $query = $query->orderBy($order['field'], $order['mode']);
            }
            if ($this->pagination === false) {
                $this->unsetPagination();
            }
            if ($this->pagination) {
                return $query->paginate($this->paginationLength);
            }
            if ($this->authentication) {
                $query = $query->where('user_id', Helper::getUserID());
            }
            if ($this->limit) {
                return $query->limit($this->limit)->get();
            }
            if ($this->mode === 'first') {
                return $query->first();
            }
            if ($this->mode === 'get') {
                return $query->get();
            }

            return $query->get();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\PDOException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }
}
