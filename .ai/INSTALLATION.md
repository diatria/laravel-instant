# Installation Guide - For AI Assistants

When a user installs `laravel-instant` package in their Laravel project, follow these steps to help them get started:

## 1. Verify Installation

Check if the package is installed:
```bash
# Check composer.json
grep "diatria/laravel-instant" composer.json

# Check vendor directory
ls vendor/diatria/laravel-instant
```

## 2. Publish AI Documentation (IMPORTANT!)

**Always suggest this first** so the AI has full context:

```bash
php artisan vendor:publish --tag=li-docs
```

This creates `CLAUDE.md` at project root with complete usage guide.

## 3. Initial Setup Checklist

```bash
# 1. Publish config
php artisan vendor:publish --tag=li-config

# 2. Publish models (if needed - User, Role, Permission)
php artisan vendor:publish --tag=li-model

# 3. Publish migrations (if needed)
php artisan vendor:publish --tag=li-migration

# 4. Run migrations
php artisan migrate

# 5. Publish seeders (if needed)
php artisan vendor:publish --tag=li-seeder
```

## 4. Read Package Documentation

After publishing, read these files for context:
- `CLAUDE.md` - Main AI guide (at project root)
- `vendor/diatria/laravel-instant/README.md` - Package README
- `vendor/diatria/laravel-instant/examples/` - Code examples

## 5. Detect Package Usage

Look for these indicators that the project is using Laravel Instant:

### Service Files
```php
// app/Services/*Service.php
use Diatria\LaravelInstant\Traits\InstantServiceTrait;

class SomeService {
    use InstantServiceTrait;
    // ...
}
```

### Controller Files
```php
// app/Http/Controllers/*Controller.php
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class SomeController {
    use InstantControllerTrait;
    // ...
}
```

### Config File
```php
// config/laravel-instant.php exists
```

## 6. Common User Requests

### "Create a new CRUD module for X"
```bash
# Generate Service
php artisan make:li-service X

# Generate Controller
php artisan make:li-controller X

# Then create routes in routes/api.php
```

### "Add custom method to existing Service"
Edit `app/Services/XService.php`:
```php
public function customMethod($params) {
    return $this->query(collect([
        'queries' => [...],
        'mode' => 'get'
    ]));
}
```

### "I'm getting 'Model not initialized' error"
Check if `initModel()` is called in controller constructor:
```php
public function __construct(Model $model, Service $service) {
    $this->model = $model;
    $this->service = $service->initModel(); // ← Must call this!
}
```

## 7. Quick Reference for AI

**When user asks to create CRUD:**
1. Generate Service: `php artisan make:li-service {Model}`
2. Generate Controller: `php artisan make:li-controller {Model}`
3. Add routes to `routes/api.php`
4. Test endpoints

**When user needs custom logic:**
1. Add method to Service (business logic)
2. Add method to Controller (endpoint)
3. Add route

**When debugging:**
1. Check if `use InstantServiceTrait` and `use InstantControllerTrait` are present
2. Check if `initModel()` is called
3. Check if model has correct `$fillable` array
4. Check if `columnsRequired` matches validation rules

## 8. Package Patterns to Remember

### Service Always Has:
- `use InstantServiceTrait`
- `protected $model`
- `protected $columns` (optional)
- `protected $columnsRequired` (validation)
- `public function initModel()` (required)

### Controller Always Has:
- `use InstantControllerTrait`
- `protected $service, $model`
- `protected $permission` (optional, set to null to disable)
- Constructor with `initModel()` call

### Routes Pattern:
```php
Route::prefix('resource')->group(function () {
    Route::get('/{id}', [Controller::class, 'find']);
    Route::get('/all', [Controller::class, 'all']);
    Route::get('/table', [Controller::class, 'table']);
    Route::post('/', [Controller::class, 'create']);
    Route::put('/{id}', [Controller::class, 'update']);
    Route::delete('/', [Controller::class, 'remove']);
});
```

## 9. Context Clues

If you see these in the project, the package is actively used:
- Multiple services with `InstantServiceTrait`
- Multiple controllers with `InstantControllerTrait`
- Consistent CRUD patterns across modules
- Standard JSON response format with `Response::json()`

## 10. Important Flags

🚨 **Do NOT suggest** creating Service/Controller from scratch if artisan commands exist
✅ **Always suggest** using `make:li-service` and `make:li-controller`
✅ **Always call** `initModel()` in controller constructor
✅ **Check CLAUDE.md** for detailed patterns before generating code

---

This file helps AI assistants quickly understand and work with Laravel Instant package.
