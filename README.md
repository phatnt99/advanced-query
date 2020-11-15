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
| |
| --UserFilter.php
| |
--Sorts
| |
| --UserSort.php
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
A small tip with date filter, if you want to filter date in range (from...to). Try that:
```php
    protected $filterDate = [
        'from.created_at',
        'to.created_at'
    ];
```
### Custom filter
Sometimes your filter work with complicated logic (like filtering on a relationship attribute), the filter class help you custom your own function to perform complex filter
```php
protected function project($projectId)
{
	return $this->query->whereHas('projects', function ($query) use ($projectId) {
            $query->where('project_id', '=', $projectId);
        });
}
```
Then use the function name like attribute name in query string.
### Using in query string
You can pass the attribute along with value want to be filter by **filters** key:
```
GET /users?filters[name]=John&filters[created_at]=2020-02-02
```
## Sort
### Default sort
The concrete Sort class has attribute defaultSort which let you define sortable attributes.
```php
protected $defaultSorts = [
        'full_name',
        'nick_name',
        'dob',
        'email',
        'phone_number',
];
```
For attribute has constant direction, use enum SortDirection for its value
```php
protected $defaultSorts = [
		...
        'dob' => SortDirection::DESCENDING,
];
```
**Note**: If the attribute has both a default direction (like above) and a direction in the query string, it will treat direction in the query string as a higher priority.
### Using in query string
I use **sort** key with prefix - to perform descending sort (none is ascending):
```
GET /users?sort=id,-dob
```
## Query
This is the class that binds the main functions of the package. You must define some important attributes that point to dependendcy classes
```php
/**
* Your model class
* @var string
*/
protected $model = User::class;

/**
* Your filter class
* @var string
*/
protected $filter = UserFilter::class;

/**
* Your sort class
* @var string
*/
protected $sort = UserSort::class;
```
If you use the full command (with --fs option and correct model) it will automatically bind these value for you (or just model class if not using --fs option).


Just specify the dependent classes (with the full handler in each dependency) and you can use the main power of the package.

```php
// UserController

public function index(UserQuery $query)
{
   return response()->json(
			$query->filter()
				->sort()
				->paginate());
}
```
### Allowed attributes
In case you want to restrict only some attributes allowed to be filtered or sorted, pass these attribute to **allows** argument (the 2nd paramater)
```php
   return response()->json(
			$query->filter(null, ['id', 'name'])
				->sort(null, ['created_at'])
				->paginate());
```
### Eloquent Query & Advanced Query
You can use Eloquent methods (where, whereHas,...) directly with an instance of Query class but it will lost the chain (chaining methods). A better way, define your own method which use these Eloquent method and then return the Query instance
```php
// UserQuery

/**
* Advanced Query
*/
public function verifiedUser() {
	$this->query->whereNotNull('email_verify_at');
	return $this;
}
```
### Paginate
I use default paginate of Eloquent model which allow you pass the index of expected page into ``paginate()`` (default is 1).
## Other usage
You can use each feature independently.
### Filter & Sort independent
You must set the query attribute to use these independently. Example (the same method for sort):
```php
$filter = new \App\Queries\Filters\UserFilter();
$filter->setQuery(User::query());
```
Instead of passing attributes (with value) into the query string, you can pass it into `setAllowAttrs` method:
```php
$filter = new \App\Queries\Filters\UserFilter();
$filter->setQuery(User::query())
	   ->setAllowAttrs(['name' => 'John'])
	   ->getCollection();
```
Then you can easily get collection result with `getCollection` method.

By the way, the package provides 2 separate commands for filter and sort:
```
php artisan make:filter UserFilter
php artisan make:sort UserSort
```
**Happy Coding!**
# Contributing
If you find some issue or want to make it better with your code, feel free to make PR or Issue :)
# License
The MIT License (MIT)
