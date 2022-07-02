# Column sorting for Laravel 8

Package for  making Eloquent models filterable.

# Setup

## Composer

Pull this package in through Composer

```sh
composer require and48/table-filters
```
## Publish migrations

Publish the package migrations files to your application.

```sh
php artisan vendor:publish --provider="AND48\TableFilters\TableFiltersServiceProvider" --tag="migrations"
```

Run migrations.

```sh
php artisan migrate
```

## Publish configuration

Publish the package configuration file to your application.

```sh
php artisan vendor:publish --provider="AND48\TableFilters\TableFiltersServiceProvider" --tag="config"
```

See configuration file [(`config/filters.php`)](https://github.com/AND48/table-filters/blob/master/config/config.php) yourself and make adjustments as you wish.

# Usage

Create filters for model.

```php
use AND48\TableFilters\Models\Filter;
...
User::addFilters([
    ['field' =>'id', 'type' => Filter::TYPE_NUMBER, 'caption' => 'ID'],
    ['field' =>'name', 'type' => Filter::TYPE_STRING, 'caption' => 'Name'],
    ['field' =>'birthday', 'type' => Filter::TYPE_DATE, 'caption' => 'Birthday'],
    ['field' =>'is_blocked', 'type' => Filter::TYPE_BOOLEAN, 'caption' => 'Is blocked'],
    ['field' =>'balance', 'type' => Filter::TYPE_NUMBER, 'caption' => 'Balance'],
    ['field' =>'status', 'type' => Filter::TYPE_ENUM, 'caption' => 'Status'],
    ['field' =>'parent_id', 'type' => Filter::TYPE_SOURCE, 'caption' => 'Parent user'],
]);
...
}
```
Use **Filterable** trait inside your *Eloquent* model(s).

Get your filters.
```php
$filters = User::filterList(true, [
                'status' => ['new','confirmed', 'verified', 'active', 'suspended']]
```