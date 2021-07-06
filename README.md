# Laravel Idempotent Package

Laravel Idempotent Package

## Installation

Use the package manager [composer](https://getcomposer.org/) to install laravel idempotent package.

```bash
composer require yeknava/laravel-idempotent
```

## Usage
Run this command in your terminal:

```bash
php artisan vendor:publish
```

Then add this to your Kernel.php in app/Http folder.

```php
protected $routeMiddleware = [
    //...
    'idempotent' => \Yeknava\Laravel-Idempotent\Idempotent::class,
];
```

then you can use this on your routes like this:

```php
Route::middleware('idempotent')->post('/', function (Request $request) {
    return 'hi';
});

```
## Config
```php
<?php

return [
    'expires_in' => 86400, //24h
    'force' => true, //force idempotent key exists (it wont force on GET method anyway)
    'skip_http_methods' => [
        'HEAD',
        'OPTIONS',
    ],
    'header_key' => 'Idempotency-Key'
];

```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.



## License
[MIT](https://choosealicense.com/licenses/mit/)
