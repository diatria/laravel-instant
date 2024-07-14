# Laravel Instant

## Tentang
Laravel Instant dibuat untuk mempermudah dan mempercepat pengerjaan pembuatan module

## Instalasi

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

Tambahkan kode dibawan ini pada file `composer.json` di bagian `autoload > psr-4`

```json
"autoload": {
  "psr-4": {
    "Diatria\\LaravelInstant\\": "vendor/diatria/laravel-instant/src"
  }
},
```
