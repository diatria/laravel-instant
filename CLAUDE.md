# Laravel Instant - AI Assistant Guide

## Package Overview
Laravel Instant is a CRUD acceleration package that provides ready-to-use traits for Controllers and Services, eliminating boilerplate code for standard CRUD operations.

## Core Concepts

### 1. Architecture Pattern
```
Controller (InstantControllerTrait) → Service (InstantServiceTrait) → Model → Database
```

### 2. Main Traits
- **InstantControllerTrait**: Provides `find()`, `all()`, `table()`, `create()`, `update()`, `remove()`
- **InstantServiceTrait**: Provides `find()`, `all()`, `table()`, `store()`, `remove()`, `query()`

## Quick Start Guide

### Generate New CRUD Module

**Step 1: Generate Service**
```bash
php artisan make:li-service {ModelName}
# Example: php artisan make:li-service Product
# Creates: app/Services/ProductService.php
```

**Step 2: Generate Controller**
```bash
php artisan make:li-controller {ModelName}
# Example: php artisan make:li-controller Product
# Creates: app/Http/Controllers/ProductController.php
```

**Step 3: Add Routes**
```php
// routes/api.php
Route::prefix('products')->group(function () {
    Route::get('/{id}', [ProductController::class, 'find']);
    Route::get('/all', [ProductController::class, 'all']);
    Route::get('/table', [ProductController::class, 'table']);
    Route::post('/', [ProductController::class, 'create']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/', [ProductController::class, 'remove']);
});
```

## Code Templates

### Service Template
```php
<?php
namespace App\Services;

use App\Models\Product;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;

class ProductService
{
    use InstantServiceTrait;

    protected $model;
    protected $paginationPath = '/products/table';

    // Columns to display in responses
    protected $columns = ['name', 'price', 'stock', 'category'];

    // Required fields for validation
    protected $columnsRequired = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
    ];

    public function initModel()
    {
        $this->model = new Product();
        return $this;
    }

    // Add custom methods here if needed
}
```

### Controller Template
```php
<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class ProductController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;

    // Permission configuration
    protected $permission = [
        "create" => "can_create_product",
        "view" => "can_view_product",
        "update" => "can_update_product",
        "delete" => "can_delete_product",
    ];

    public function __construct(Product $model, ProductService $service)
    {
        $this->model = $model;
        $this->service = $service->initModel();
    }

    // InstantControllerTrait provides:
    // - find(Request $request)
    // - all(Request $request)
    // - table(Request $request)
    // - create(Request $request)
    // - update(Request $request)
    // - remove(Request $request)

    // Add custom methods here if needed
}
```

## API Request Examples

### Find by ID
```
GET /api/products/1?relations[]=category&relations[]=supplier
```

### Get All
```
GET /api/products/all?relations[]=category
```

### Get Table (Paginated)
```
GET /api/products/table?page=1&pagination_length=10&order=created_at:desc
```

**With Filters:**
```
GET /api/products/table?queries[0][field]=name&queries[0][value]=Laptop&queries[0][strict]=false
```

### Create
```
POST /api/products
{
  "name": "Product Name",
  "price": 99.99,
  "stock": 100
}
```

### Update
```
PUT /api/products/1
{
  "name": "Updated Name",
  "price": 89.99
}
```

### Delete (Single)
```
DELETE /api/products
{
  "id": 1
}
```

### Delete (Multiple)
```
DELETE /api/products
{
  "id": [1, 2, 3]
}
```

## Query Parameters

### Common Parameters
- `id`: Integer - Record ID
- `relations[]`: Array - Eager load relationships
- `relations_count[]`: Array - Count relationships
- `pagination_length`: Integer - Items per page (default: 10)
- `page`: Integer - Page number
- `order`: String - Format: "field:asc" or "field:desc"

### Query Filters
```php
queries[0][field] = 'name'        // Column name
queries[0][value] = 'Product'     // Search value
queries[0][strict] = false        // false = LIKE search, true = exact match
queries[0][op] = 'ne'            // Optional: 'ne' for not equal
```

## Advanced Service Usage

### Custom Query Method
```php
public function getActiveProducts()
{
    return $this->query(collect([
        'queries' => [
            ['field' => 'status', 'value' => 'active', 'strict' => true]
        ],
        'order' => 'created_at:desc',
        'relations' => ['category'],
        'mode' => 'get'
    ]));
}
```

### Store with firstOrCreate
```php
$data = $this->service->store(
    collect($request->all()),
    ['first_or_create' => true]
);
```

### Disable Duplicate by ref_id
```php
$this->service->config(['disable_duplicate_ref_id' => true])
    ->store(collect($request->all()));
```

## Response Format

### Success Response
```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Data berhasil diambil",
  "data": {...},
  "data_count": 0
}
```

### Error Response
```json
{
  "code": "APPLICATION_ERROR",
  "internal_code": 500,
  "message": "Error message here",
  "data": null
}
```

## Permission System

Permissions are automatically checked in all CRUD operations. To bypass permission checks, set `$this->permission = null` or remove the permission array.

## Important Notes for AI Assistants

1. **Always use the Artisan commands** to generate Service and Controller - don't create from scratch
2. **Service must have `initModel()` method** - This is required for the trait to work
3. **Controller constructor must call `$service->initModel()`** - Initializes the model instance
4. **columnsRequired in Service** - Used for automatic validation
5. **All controller methods have automatic transaction handling** - create/update/remove are wrapped in DB transactions
6. **Permission checking is automatic** - Set to `null` to disable
7. **The `store()` method handles both create and update** - Detects ID automatically
8. **Response formatting is standardized** - Use `Response::json()` for consistency

## Common Patterns

### Adding Custom Business Logic
Add custom methods in Service, call from Controller:
```php
// Service
public function calculateTotalStock()
{
    return $this->model->sum('stock');
}

// Controller
public function getTotalStock()
{
    try {
        $total = $this->service->calculateTotalStock();
        return Response::json(['total' => $total]);
    } catch (ErrorException $e) {
        return Response::errorJson($e);
    }
}
```

### Working with Relationships
```php
// In Service
protected $responseFormatRelations = ['category', 'supplier'];

// Request with relations
GET /api/products/1?relations[]=category&relations[]=supplier
```

## File Locations
- **Controllers**: `app/Http/Controllers/`
- **Services**: `app/Services/`
- **Models**: `app/Models/`
- **Config**: `config/laravel-instant.php`

## Configuration
```bash
# Publish config
php artisan vendor:publish --tag=li-config

# Publish models (User, Role, Permission)
php artisan vendor:publish --tag=li-model

# Publish migrations
php artisan vendor:publish --tag=li-migration

# Publish seeders
php artisan vendor:publish --tag=li-seeder
```

## When AI Should Use This Package

✅ **Use Laravel Instant when:**
- Creating standard CRUD operations
- Building REST APIs
- Need consistent response format
- Want automatic permission handling
- Need quick scaffolding

❌ **Don't use when:**
- Complex business logic that doesn't fit CRUD pattern
- Need heavily customized queries
- Simple single-file controllers are sufficient

## Troubleshooting

**Service method not found**: Ensure `use InstantServiceTrait;` is present
**Model not initialized**: Check `initModel()` is called in constructor
**Permission denied**: Check permission array or set to `null` to bypass
**Validation fails**: Check `columnsRequired` array in Service

---

**Generated files follow consistent patterns. Always check existing generated code before creating new modules.**
