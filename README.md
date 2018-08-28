# conv-laravel

MySQL migration query auto generate on Laravel/Lumen


## Install

1. require library (Laravel/Lumen)
```
composer require "howyi/conv-laravel" --dev
```

2. publish config (only Laravel)
```
php artisan vendor:publish --provider="Conv\Laravel\ConvServiceProvider"
```

3. schema initialize (Laravel/Lumen)
```
php artisan conv:reflect
```
generate CREATE queries on `database/schemas`

## Usage
 
1. edit CREATE query (in schema directory)
2. `php artisan conv:generate` to generate migrations from actual Database - CREATE queries
3. `php artisan migrate` to migration
