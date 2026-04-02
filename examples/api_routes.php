<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/**
 * Example API Routes for Laravel Instant Package
 *
 * Add these routes to routes/api.php in your Laravel project
 */

Route::prefix('products')->group(function () {

    // ============================================
    // AUTOMATIC CRUD ROUTES (from InstantControllerTrait)
    // ============================================

    /**
     * Get single product by ID with optional relations
     *
     * Examples:
     * GET /api/products/1
     * GET /api/products/1?relations[]=category&relations[]=images
     * GET /api/products/1?relations_count[]=reviews
     */
    Route::get('/{id}', [ProductController::class, 'find']);

    /**
     * Get all products (no pagination)
     *
     * Examples:
     * GET /api/products/all
     * GET /api/products/all?relations[]=category
     */
    Route::get('/all', [ProductController::class, 'all']);

    /**
     * Get products with pagination (datatable)
     *
     * Examples:
     * GET /api/products/table?page=1&pagination_length=10
     * GET /api/products/table?page=1&order=name:asc
     * GET /api/products/table?queries[0][field]=name&queries[0][value]=Laptop&queries[0][strict]=false
     * GET /api/products/table?queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true
     */
    Route::get('/table', [ProductController::class, 'table']);

    /**
     * Create new product
     *
     * POST /api/products
     * Body:
     * {
     *   "name": "Product Name",
     *   "sku": "PRD-001",
     *   "price": 99.99,
     *   "stock": 100,
     *   "category_id": 1,
     *   "status": "active"
     * }
     */
    Route::post('/', [ProductController::class, 'create']);

    /**
     * Update existing product
     *
     * PUT /api/products/1
     * Body:
     * {
     *   "name": "Updated Name",
     *   "price": 89.99,
     *   "stock": 50
     * }
     */
    Route::put('/{id}', [ProductController::class, 'update']);

    /**
     * Delete single product
     *
     * DELETE /api/products
     * Body: { "id": 1 }
     */
    Route::delete('/', [ProductController::class, 'remove']);

    /**
     * Delete multiple products
     *
     * DELETE /api/products
     * Body: { "id": [1, 2, 3] }
     */


    // ============================================
    // CUSTOM ROUTES (additional business logic)
    // ============================================

    /**
     * Get products by category
     *
     * GET /api/products/category?category_id=1
     */
    Route::get('/category', [ProductController::class, 'getByCategory']);

    /**
     * Get low stock products
     *
     * GET /api/products/low-stock?threshold=10
     */
    Route::get('/low-stock', [ProductController::class, 'getLowStock']);

    /**
     * Update product stock
     *
     * PATCH /api/products/1/stock
     * Body:
     * {
     *   "quantity": 10,
     *   "operation": "add"  // or "subtract"
     * }
     */
    Route::patch('/{id}/stock', [ProductController::class, 'updateStock']);
});


/**
 * QUERY PARAMETERS REFERENCE
 * ============================================
 *
 * 1. Relations (Eager Loading)
 *    ?relations[]=category&relations[]=images
 *
 * 2. Relations Count
 *    ?relations_count[]=reviews&relations_count[]=orders
 *
 * 3. Pagination
 *    ?page=1&pagination_length=10
 *
 * 4. Ordering
 *    ?order=created_at:desc
 *    ?order=name:asc
 *
 * 5. Filtering (queries)
 *    ?queries[0][field]=name&queries[0][value]=Laptop&queries[0][strict]=false
 *
 *    Query Structure:
 *    - field: Column name to search
 *    - value: Value to search for
 *    - strict: true = exact match (=), false = LIKE search (%)
 *    - op: 'ne' for not equal (optional)
 *
 * 6. Multiple Filters
 *    ?queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true
 *    &queries[1][field]=price&queries[1][value]=100&queries[1][op]=ne
 *
 * 7. Combined Example
 *    /api/products/table?page=1&pagination_length=20
 *    &queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true
 *    &queries[1][field]=name&queries[1][value]=phone&queries[1][strict]=false
 *    &relations[]=category
 *    &relations_count[]=reviews
 *    &order=created_at:desc
 */
