# Laravel Instant - Common Patterns & Best Practices

This document outlines proven patterns and best practices when using Laravel Instant package.

## 🏗️ Project Structure

```
app/
├── Http/Controllers/
│   ├── ProductController.php        # Use InstantControllerTrait
│   ├── CategoryController.php
│   └── OrderController.php
├── Services/
│   ├── ProductService.php          # Use InstantServiceTrait
│   ├── CategoryService.php
│   └── OrderService.php
└── Models/
    ├── Product.php
    ├── Category.php
    └── Order.php
```

## ✅ Best Practices

### 1. Service Layer Pattern

**DO:** Separate business logic in Service
```php
// ProductService.php
public function getActiveProducts()
{
    return $this->query(collect([
        'queries' => [
            ['field' => 'status', 'value' => 'active', 'strict' => true]
        ],
        'mode' => 'get'
    ]));
}

public function decreaseStock($productId, $quantity)
{
    $product = $this->model->find($productId);
    if ($product->stock < $quantity) {
        throw new ErrorException('Insufficient stock', 400);
    }
    $product->stock -= $quantity;
    $product->save();
    return $product;
}
```

**DON'T:** Put business logic in Controller
```php
// ❌ Bad - Controller has business logic
public function decreaseStock(Request $request)
{
    $product = Product::find($request->id);
    $product->stock -= $request->quantity;
    $product->save();
}
```

### 2. Validation Pattern

**DO:** Define validation in Service `columnsRequired`
```php
// Service
protected $columnsRequired = [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'price' => 'required|numeric|min:0'
];
```

**DON'T:** Validate in Controller
```php
// ❌ Bad
public function create(Request $request)
{
    $request->validate([...]); // Don't do this
}
```

### 3. Response Pattern

**DO:** Use standardized Response
```php
use Diatria\LaravelInstant\Utils\Response;

return Response::json($data, 'Success message');
return Response::errorJson($exception);
```

**DON'T:** Return raw responses
```php
// ❌ Bad
return response()->json(['data' => $data]);
```

### 4. Error Handling Pattern

**DO:** Wrap in try-catch with ErrorException
```php
public function customMethod()
{
    try {
        // Logic here
        return Response::json($data);
    } catch (ErrorException $e) {
        return Response::errorJson($e);
    } catch (\Exception $e) {
        return Response::errorJson($e);
    }
}
```

### 5. Permission Pattern

**DO:** Define permissions in Controller
```php
protected $permission = [
    "create" => "can_create_product",
    "view" => "can_view_product",
    "update" => "can_update_product",
    "delete" => "can_delete_product",
];
```

To disable: `protected $permission = null;`

### 6. Relations Pattern

**DO:** Define default relations in Service
```php
protected $responseFormatRelations = ['category', 'images', 'reviews'];
```

**DO:** Allow dynamic relations via query params
```php
GET /api/products/1?relations[]=category&relations[]=reviews
```

### 7. Query Building Pattern

**DO:** Use query() method for complex queries
```php
public function searchProducts($keyword, $categoryId = null)
{
    $queries = [
        ['field' => 'name', 'value' => $keyword, 'strict' => false]
    ];

    if ($categoryId) {
        $queries[] = ['field' => 'category_id', 'value' => $categoryId, 'strict' => true];
    }

    return $this->query(collect([
        'queries' => $queries,
        'relations' => ['category'],
        'order' => 'created_at:desc',
        'mode' => 'get'
    ]));
}
```

## 🔄 Common Patterns

### Pattern 1: Master-Detail CRUD

```php
// OrderService.php
public function createWithItems($orderData, $items)
{
    DB::beginTransaction();
    try {
        // Create order
        $order = $this->store(collect($orderData));

        // Create order items
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order['id'],
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        DB::commit();
        return $order;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Pattern 2: Soft Delete with Archive

```php
public function archive($id)
{
    $data = $this->model->find($id);
    $data->status = 'archived';
    $data->archived_at = now();
    $data->save();
    return $data;
}
```

### Pattern 3: Bulk Operations

```php
public function bulkUpdate(array $ids, array $data)
{
    DB::beginTransaction();
    try {
        $updated = $this->model->whereIn('id', $ids)->update($data);
        DB::commit();
        return $updated;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new ErrorException($e->getMessage(), 500);
    }
}
```

### Pattern 4: Export/Import

```php
public function exportToCSV()
{
    $data = $this->all(collect());
    // Convert to CSV logic
    return $csvData;
}

public function importFromCSV($file)
{
    // Parse CSV and bulk insert
    foreach ($rows as $row) {
        $this->store(collect($row));
    }
}
```

### Pattern 5: Nested Filters

```php
// Advanced filtering
GET /api/products/table?
    queries[0][field]=category_id&queries[0][value]=1&queries[0][strict]=true&
    queries[1][field]=price&queries[1][value]=100000&queries[1][strict]=false&
    queries[2][field]=status&queries[2][value]=inactive&queries[2][op]=ne
```

## 🚫 Common Pitfalls

### ❌ Pitfall 1: Forgetting initModel()
```php
// Wrong
public function __construct(Product $model, ProductService $service) {
    $this->service = $service; // ❌ Missing initModel()
}

// Correct
public function __construct(Product $model, ProductService $service) {
    $this->service = $service->initModel(); // ✅
}
```

### ❌ Pitfall 2: Not Using Collections in Service
```php
// Wrong
$this->service->store($request->all()); // ❌ Array

// Correct
$this->service->store(collect($request->all())); // ✅ Collection
```

### ❌ Pitfall 3: Mixing Query Modes
```php
// Wrong - trying to paginate with mode='first'
$this->query(collect([
    'pagination' => true,
    'mode' => 'first' // ❌ Conflict
]));

// Correct
$this->query(collect([
    'pagination' => true,
    'mode' => 'get' // ✅
]));
```

### ❌ Pitfall 4: Wrong fillable Configuration
```php
// Model - must have fillable
class Product extends Model {
    protected $fillable = ['name', 'price', 'stock']; // ✅ Required
}
```

### ❌ Pitfall 5: Not Handling Transactions
```php
// Wrong - no transaction for critical operations
public function complexOperation() {
    $this->store(...);
    SomeModel::create(...);
    // ❌ What if second operation fails?
}

// Correct
public function complexOperation() {
    DB::beginTransaction();
    try {
        $this->store(...);
        SomeModel::create(...);
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

## 🎯 Performance Tips

### 1. Use Eager Loading
```php
// Bad - N+1 query problem
GET /api/products/table

// Good - with relations
GET /api/products/table?relations[]=category&relations[]=brand
```

### 2. Limit Columns
```php
// Service
protected $columns = ['id', 'name', 'price']; // Only needed columns
```

### 3. Use Relations Count
```php
// Instead of loading all reviews
GET /api/products/1?relations_count[]=reviews
// Returns: { ..., reviews_count: 150 }
```

### 4. Proper Pagination
```php
// Always use pagination for large datasets
GET /api/products/table?pagination_length=50
```

## 📝 Naming Conventions

### Services
- `{Model}Service.php` (e.g., `ProductService.php`)
- Method names: `getActive()`, `searchBy()`, `updateStatus()`

### Controllers
- `{Model}Controller.php` (e.g., `ProductController.php`)
- Method names: match service or REST convention

### Permissions
- `can_create_{resource}` (e.g., `can_create_product`)
- `can_view_{resource}`
- `can_update_{resource}`
- `can_delete_{resource}`

## 🔍 Testing Patterns

### Unit Test for Service
```php
public function test_can_get_active_products()
{
    $service = new ProductService();
    $service->initModel();

    $products = $service->getActiveProducts();

    $this->assertNotEmpty($products);
    $this->assertEquals('active', $products[0]->status);
}
```

### Integration Test for Controller
```php
public function test_can_create_product()
{
    $response = $this->post('/api/products', [
        'name' => 'Test Product',
        'price' => 99.99
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'code',
        'message',
        'data' => ['id', 'name', 'price']
    ]);
}
```

## 🎨 Advanced Patterns

### Custom Response Formatter
```php
// Create app/Http/Responses/ProductResponse.php
class ProductResponse extends ResponseFormat {
    public function object($data) {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'formatted_price' => 'Rp ' . number_format($data->price)
        ];
    }
}

// Use in Service
protected $responseFormatClass = ProductResponse::class;
```

### Config-based Behavior
```php
// Service
public function __construct()
{
    if (config('app.auto_archive')) {
        $this->config(['disable_duplicate_ref_id' => true]);
    }
}
```

### Event-Driven Pattern
```php
public function store($params, array $config = []): Collection
{
    $result = parent::store($params, $config);

    // Fire event after save
    event(new ProductCreated($result));

    return $result;
}
```

---

Follow these patterns for consistent, maintainable code with Laravel Instant! 🚀
