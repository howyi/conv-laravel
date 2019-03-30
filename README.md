# conv-laravel

MySQL migration query auto generate on Laravel/Lumen
<p align="center">
 <img src="https://res.cloudinary.com/practicaldev/image/fetch/s--RjgVuhNV--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_66%2Cw_880/https://thepracticaldev.s3.amazonaws.com/i/hpm2d58qbtlmwmw14lgl.gif">
</p>

## Install

1. require library (Laravel/Lumen)
```
composer require "howyi/conv-laravel" --dev
```

2. publish config (only Laravel)
```
php artisan vendor:publish --provider="Howyi\ConvLaravel\ConvServiceProvider"
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
