<?php

namespace App\Services;

use App\Models\Product;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;

/**
 * Example Product Service using Laravel Instant
 *
 * This service demonstrates how to use InstantServiceTrait
 * for standard CRUD operations with minimal code.
 */
class ProductService
{
    use InstantServiceTrait;

    /**
     * The model instance
     * @var \App\Models\Product
     */
    protected $model;

    /**
     * Pagination path for table endpoint
     * @var string
     */
    protected $paginationPath = '/products/table';

    /**
     * Columns to display in responses
     * Leave empty to show all columns
     * @var array
     */
    protected $columns = [
        'name',
        'sku',
        'price',
        'stock',
        'category_id',
        'description',
        'status'
    ];

    /**
     * Validation rules for store/update operations
     * @var array
     */
    protected $columnsRequired = [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:products,sku',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'category_id' => 'required|exists:categories,id',
        'status' => 'in:active,inactive'
    ];

    /**
     * Default relationships to load
     * @var array
     */
    protected $responseFormatRelations = ['category', 'images'];

    /**
     * Initialize model instance
     * This method is required for InstantServiceTrait
     */
    public function initModel()
    {
        $this->model = new Product();
        return $this;
    }

    /**
     * Custom method: Get products by category
     *
     * Example of adding custom business logic
     */
    public function getByCategory($categoryId)
    {
        return $this->query(collect([
            'queries' => [
                ['field' => 'category_id', 'value' => $categoryId, 'strict' => true],
                ['field' => 'status', 'value' => 'active', 'strict' => true]
            ],
            'relations' => ['category', 'images'],
            'order' => 'name:asc',
            'mode' => 'get'
        ]));
    }

    /**
     * Custom method: Get low stock products
     */
    public function getLowStock($threshold = 10)
    {
        return $this->model
            ->where('stock', '<=', $threshold)
            ->where('status', 'active')
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Custom method: Update stock
     */
    public function updateStock($productId, $quantity, $operation = 'add')
    {
        $product = $this->model->find($productId);

        if (!$product) {
            throw new \Diatria\LaravelInstant\Utils\ErrorException('Product not found', 404);
        }

        if ($operation === 'add') {
            $product->stock += $quantity;
        } elseif ($operation === 'subtract') {
            if ($product->stock < $quantity) {
                throw new \Diatria\LaravelInstant\Utils\ErrorException('Insufficient stock', 400);
            }
            $product->stock -= $quantity;
        }

        $product->save();
        return $product;
    }
}
