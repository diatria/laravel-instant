[![AI-Friendly](https://img.shields.io/badge/AI-Friendly-success)](CLAUDE.md)

# Laravel Instant

## Tentang

Laravel Instant adalah package Laravel yang mempermudah dan mempercepat pembuatan CRUD module dengan menyediakan Trait dan Command Generator yang siap pakai. Package ini mengurangi boilerplate code hingga 70% dan memberikan struktur yang konsisten untuk Controller dan Service layer.

## ✨ Fitur Utama

- 🚀 **Instant CRUD Operations** - Method CRUD lengkap tanpa menulis boilerplate
- 🎯 **Code Generator** - Artisan commands untuk generate Controller & Service
- 🔐 **Built-in Permission System** - Permission checking otomatis untuk setiap operasi
- 📊 **Smart Query Builder** - QueryMaker dengan filter, sorting, pagination, relations
- 🔄 **Standardized Response** - JSON response format yang konsisten
- 🔑 **JWT Authentication** - Built-in JWT token handling
- 👥 **Role & Permission Management** - Complete RBAC system
- 🤖 **AI-Friendly** - Documentation optimized for AI assistants (Claude, ChatGPT, etc.)

## Instalasi

### Via Composer (Recommended)
```bash
composer require diatria/laravel-instant
```

### Via Git Clone (Development)
```bash
git clone https://github.com/diatria/laravel-instant.git
```

**Untuk Laravel**
Tambahkan kode dibawah ini pada file `bootstrap/providers.php`

```php
<?php
return [
    // ...
    Diatria\LaravelInstant\LaravelInstantServiceProvider::class,
];
```

Tambahkan kode dibawan ini pada file `composer.json` di bagian:
- `autoload`
- `repositories`

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^11.9",
    // ...
    "diatria/laravel-instant": "*"
},
"autoload": {
  "psr-4": {
    "Diatria\\LaravelInstant\\": "vendor/diatria/laravel-instant/src"
  }
},

"repositories": {
    "local": {
        "type": "path",
        "url": "./vendor/diatria/laravel-instant"
    }
},
```

## 📦 Publish Assets

Setelah instalasi, publish file-file yang diperlukan:

```bash
# Publish config file
php artisan vendor:publish --tag=li-config

# Publish models (User, Role, Permission, RolePermission)
php artisan vendor:publish --tag=li-model

# Publish migrations
php artisan vendor:publish --tag=li-migration

# Publish seeders
php artisan vendor:publish --tag=li-seeder

# 🤖 Publish AI documentation (CLAUDE.md untuk AI assistants)
php artisan vendor:publish --tag=li-docs
```

## 🤖 AI Assistant Support

**Laravel Instant dirancang agar AI Assistant dapat langsung memahami cara penggunaannya!**

Setelah install package, publish dokumentasi AI:

```bash
php artisan vendor:publish --tag=li-docs
```

Ini akan membuat file `CLAUDE.md` di root project Anda. File ini berisi:
- ✅ Panduan lengkap penggunaan package
- ✅ Template code yang siap pakai
- ✅ Best practices dan pattern
- ✅ Contoh request/response API
- ✅ Troubleshooting guide

**AI assistants seperti Claude Code, GitHub Copilot, atau ChatGPT akan otomatis membaca file ini dan langsung paham cara menggunakan package!**

Lihat folder `examples/` untuk contoh code lengkap:
- `ProductService.php` - Contoh Service dengan custom methods
- `ProductController.php` - Contoh Controller dengan custom endpoints
- `api_routes.php` - Contoh routing lengkap
- `API_EXAMPLES.md` - Contoh API request dengan curl & JavaScript

## 🚀 Quick Start

### 1. Generate Service & Controller

```bash
# Generate Service
php artisan make:li-service Product

# Generate Controller
php artisan make:li-controller Product
```

### 2. Setup Routes

Tambahkan di `routes/api.php`:

```php
use App\Http\Controllers\ProductController;

Route::prefix('products')->group(function () {
    Route::get('/{id}', [ProductController::class, 'find']);
    Route::get('/all', [ProductController::class, 'all']);
    Route::get('/table', [ProductController::class, 'table']);
    Route::post('/', [ProductController::class, 'create']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/', [ProductController::class, 'remove']);
});
```

### 3. Ready to Use! 🎉

```bash
# Get all products
curl http://localhost:8000/api/products/all

# Get products with pagination
curl http://localhost:8000/api/products/table?page=1

# Create product
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Product 1","price":99.99}'
```

## 📖 Penggunaan Lengkap

### Service Example

File: `app/Services/ProductService.php`

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

    // Kolom yang ditampilkan
    protected $columns = ['name', 'price', 'stock'];

    // Validasi untuk create/update
    protected $columnsRequired = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric'
    ];

    public function initModel()
    {
        $this->model = new Product();
        return $this;
    }
}
```

### Controller Example

File: `app/Http/Controllers/ProductController.php`

```php
<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class ProductController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;

    // Permission configuration (optional)
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
}
```

## 🎯 Available Methods

### Controller Methods (InstantControllerTrait)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `find()` | `GET /products/{id}` | Get single record by ID |
| `all()` | `GET /products/all` | Get all records (no pagination) |
| `table()` | `GET /products/table` | Get records with pagination |
| `create()` | `POST /products` | Create new record |
| `update()` | `PUT /products/{id}` | Update existing record |
| `remove()` | `DELETE /products` | Delete record(s) |

### Service Methods (InstantServiceTrait)

| Method | Description |
|--------|-------------|
| `find($params)` | Find single record by ID |
| `all($params)` | Get all records |
| `table($params)` | Get paginated records |
| `store($params)` | Create or update record |
| `remove($id)` | Delete record(s) |
| `query($params)` | Custom query builder |

## 🔍 Query Parameters

### Basic Parameters

```bash
# Get product with ID 1
GET /api/products/1

# With relations
GET /api/products/1?relations[]=category&relations[]=images

# With relations count
GET /api/products/1?relations_count[]=reviews
```

### Table Pagination

```bash
# Basic pagination
GET /api/products/table?page=1&pagination_length=10

# With sorting
GET /api/products/table?order=created_at:desc

# With filters
GET /api/products/table?queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true
```

### Query Filters

| Parameter | Type | Description |
|-----------|------|-------------|
| `field` | string | Column name to filter |
| `value` | mixed | Value to search |
| `strict` | boolean | `true` = exact match, `false` = LIKE search |
| `op` | string | Operator: `ne` for not equal |

### Complete Example

```bash
GET /api/products/table?\
  page=1&\
  pagination_length=20&\
  queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true&\
  queries[1][field]=name&queries[1][value]=laptop&queries[1][strict]=false&\
  relations[]=category&\
  order=price:desc
```

## 📋 Response Format

### Success Response

```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Data berhasil diambil",
  "data": {
    "id": 1,
    "name": "Product Name",
    "price": 99.99
  },
  "data_count": 0
}
```

### Error Response

```json
{
  "code": "APPLICATION_ERROR",
  "internal_code": 500,
  "message": "Error message",
  "data": null
}
```

## 🔐 Permission System

Package ini include permission checking otomatis. Untuk mengaktifkan:

```php
protected $permission = [
    "create" => "can_create_product",
    "view" => "can_view_product",
    "update" => "can_update_product",
    "delete" => "can_delete_product",
];
```

Untuk disable permission checking:
```php
protected $permission = null;
```

## 🎨 Advanced Usage

### Custom Service Methods

```php
class ProductService
{
    use InstantServiceTrait;

    // Custom method
    public function getByCategory($categoryId)
    {
        return $this->query(collect([
            'queries' => [
                ['field' => 'category_id', 'value' => $categoryId, 'strict' => true]
            ],
            'relations' => ['category'],
            'mode' => 'get'
        ]));
    }
}
```

### Custom Controller Endpoints

```php
class ProductController extends Controller
{
    use InstantControllerTrait;

    // Custom endpoint
    public function getByCategory(Request $request)
    {
        try {
            $products = $this->service->getByCategory($request->category_id);
            return Response::json($products);
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        }
    }
}
```

## 📚 Documentation

Untuk dokumentasi lengkap, lihat:
- **`CLAUDE.md`** - AI Assistant Guide (comprehensive usage guide)
- **`examples/`** - Working code examples
- **`examples/API_EXAMPLES.md`** - API request examples with curl & JavaScript

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

MIT License

## 👨‍💻 Author

**Dimas Adi Satria**

---

**Happy Coding! 🚀**

🤖 Working with AI Assistants?  
Run `php artisan vendor:publish --tag=li-docs` to get AI-optimized documentation!
