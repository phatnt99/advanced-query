# advanced-query
A package provides an advanced query for Eloquent Laravel.
## Requirements
This package require:
- PHP 7.2+
# Installation
Install the package through composer:
```php
composer require phatnt99/advanced-query
```
## Laravel
> The package supports auto-discovery, so if you use Laravel 5.5 or later you may skip this step.

Append the following line to the `providers` key in `config/app.php` to add the service provider
```php
Phatnt99\AdvancedQuery\QueryServiceProvider::class
```
# Usage
This package support 3 key features: **filter**, **sort** and **custom query**.

To get started, run the bellow Artisan command:
```
php artisan make:query UserQuery --fs
```
**Note**: you need to define your model before running this command, the word User in that query name will use to find existed model. If no model corresponds to the name provided, the error will show and cause unexpected behavior (but you can change the correct class model after that).

The `--fs` option will create class Filter and Sort, if you just custom your query then remove this.

The above command will create 3 files in `App\Queries`:
```
-Queries
|
--Filters
 |
 --UserFilter.php
 |
 --UserSort.php
|
--UserQuery.php
```
Let deep to detail
## Filter
### Default filter
The package ship with 3 attributes to save your time. Instead of doing this:
```php
return $query->where('name','LIKE', '%'.$name.'%');
...
return $query->where('email', '=', $email);
...
return $query->whereDate('created_at', '=', date('Y-m-d'));
```
You can use this:
```php
protected $filterPartial = [
	'name'
];

protected $filterExact = [
	'email'
];

protected $filterDate = [
	'created_at'
];
```
It reduces time to write duplicate code and make the class where you perform some filter function more readable :)
### Filter date in range
A little trick with date filter, if you want to filter date in range (from...to). Try that:
```php
    protected $filterDate = [
        'from.created_at',
        'to.created_at'
    ];
```
### Custom filter
Sometimes your filter work with complicated logic (like filtering on a relationship attribute), the filter class help you custom your own function to perform complex filter
```php
protected function complex($value)
{
	something more complex than exact, partial or date..
}
```
### Using in query string
You can pass the attribute along with value want to be filter by **filters** key:
```
GET /users?filters[name]=John&filters[created_at]=2020-02-02
```
## Sort
