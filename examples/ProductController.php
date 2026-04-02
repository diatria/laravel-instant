<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

/**
 * Example Product Controller using Laravel Instant
 *
 * This controller demonstrates how to use InstantControllerTrait
 * to get instant CRUD endpoints with minimal code.
 */
class ProductController extends Controller
{
    use InstantControllerTrait;

    protected $service;
    protected $model;

    /**
     * Permission configuration
     * Set to null to disable permission checks
     *
     * @var array|null
     */
    protected $permission = [
        "create" => "can_create_product",
        "view" => "can_view_product",
        "update" => "can_update_product",
        "delete" => "can_delete_product",
    ];

    /**
     * Constructor - Inject model and service
     */
    public function __construct(Product $model, ProductService $service)
    {
        $this->model = $model;
        $this->service = $service->initModel();
    }

    /**
     * The InstantControllerTrait automatically provides these methods:
     *
     * - find(Request $request)      : GET  /products/{id}
     * - all(Request $request)       : GET  /products/all
     * - table(Request $request)     : GET  /products/table
     * - create(Request $request)    : POST /products
     * - update(Request $request)    : PUT  /products/{id}
     * - remove(Request $request)    : DELETE /products
     */

    /**
     * Custom endpoint: Get products by category
     *
     * Example of adding custom business logic
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory(Request $request)
    {
        try {
            $categoryId = $request->category_id;

            if (!$categoryId) {
                throw new ErrorException('Category ID is required', 400);
            }

            $products = $this->service->getByCategory($categoryId);
            return Response::json($products, 'Products retrieved successfully');
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }

    /**
     * Custom endpoint: Get low stock products
     */
    public function getLowStock(Request $request)
    {
        try {
            $threshold = $request->get('threshold', 10);
            $products = $this->service->getLowStock($threshold);

            return Response::json(
                $products,
                "Found {$products->count()} products with low stock"
            );
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }

    /**
     * Custom endpoint: Update stock
     */
    public function updateStock(Request $request, $id)
    {
        try {
            $quantity = $request->quantity;
            $operation = $request->operation ?? 'add'; // 'add' or 'subtract'

            if (!$quantity) {
                throw new ErrorException('Quantity is required', 400);
            }

            $product = $this->service->updateStock($id, $quantity, $operation);

            return Response::json(
                $product,
                "Stock updated successfully. Current stock: {$product->stock}"
            );
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }
}
