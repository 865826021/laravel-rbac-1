# laravel-rbac
Role based access control for laravel 5

## Installation

1) Open your composer.json and add the following. Then run `composer update`

```json
"dmitrybubyakin/laravel-rbac": "dev-master"
```

2) Open your `config/app.php` and add the following to `providers` array:

```php
DmitryBubyakin\RBAC\RBACServiceProvider::class
```

3) Run the command `php artisan vendor:publish` to publish config and migration files, then run `php artisan migrate`

4) Open `config/rbac.php` and change the following to your models:

```php
'models' => [
  'role' => DmitryBubyakin\RBAC\Models\Role::class,
  'user' => App\Models\User::class,
  'permission' => DmitryBubyakin\RBAC\Models\Permission::class,
]
```

## Configuration

### Models

#### Role and Permission

To create a Role model in your project use the following:

```php
namespace App\Models;

use DmitryBubyakin\RBAC\Models\Role as BaseRole;

class Role extends BaseRole{
	
}
```

To create a Permission model in your project use the following:

```php
namespace App\Models;

use DmitryBubyakin\RBAC\Models\Permission as BasePermission;

class Permission extends BasePermission{
	
}
```

Both role and permission have two attributes:
 - `name` &mdash; unique name for the role or the permission. Example: "add-user", "send-email", "admin", "user".
 - `description` &mdash; [Optional] description for the role or the permission.

#### User

Use the UserTrait in your User model:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use DmitryBubyakin\RBAC\Traits\UserTrait;

class User extends Authenticatable
{
    use UserTrait;

}
```

## Usage

Creating roles and permissions:

```php
//roles

//first way
$adminRole = Role::create(['name' => 'admin']);
$user->attachRole($adminRole);

//second way
//if role doesn't exists, it will be created automatically
$user->attachRole('admin');
$user->attachRole(['admin','user']);

//detach all
$user->detachRole();
$user->detachRole($adminRole);
$user->detachRole(['admin','user']);


//permissions
//first way
$canPost = Permission::create(['name' => 'can-post']);
$adminRole->attachPermission($canPost);

//second way
$adminRole->attachPermission('can-post');
$adminRole->attachPermission(['can-post','can-comment']);
//see middleware to know what second param means
$adminRole->attachPermission(['can-post','can-comment'],false);

//detach all
$adminRole->detachPermission();

$adminRole->detachPermission('can-post');
$adminRole->detachPermission(['can-post','can-comment']);


```

Check user's permissions:

```php
$user->can('can-post');
$user->can('can-update|can-delete|can-create');
$user->can(['can-update','can-delete','can-create']);
```

Check user's roles:

```php
$user->roleIs('admin');
$user->roleIs('admin|user');
$user->roleIs(['admin','user']);
```

## Blade

```php
@perm('can-create|can-update,false')
	//...
@elseperm('can-comment')
	//...
@elseperm
	//...
@endperm


@role('admin|user')
	//...
@elserole('guest')
	//...
@elserole
	//...
@endrole
```


## Middleware

If you want to use middleware, you need to add the following to routeMiddleware array in `App\Http\Kernel.php`:
```php
'rbac.can' => \DmitryBubyakin\RBAC\Middleware\Can::class,
'rbac.role' => \DmitryBubyakin\RBAC\Middleware\Role::class,
```

Both middlewares have two attributes:
 - `attributes` &mdash; roles or permissions divided by `|`. Example: "admin|user", "can-create|can-post"
 - `required` &mdash; if true then all of attributes required. If false then required at least one of them. Example: "admin|user,false" it's means that admin or user or admin and user required; "admin|user" admin and user required

Example

```php
Route::get('admin','AdminController@index')->middleware('rbac.role:admin|student');
Route::post('news','NewsController@store')->middleware('rbac.can:can-store|can-comment','rbac.role:admin');

//if second middleware parameter is true (by default) then all items are required
//if false, then required at least one of them
Route::get('admin','AdminController@index')->middleware('rbac.role:admin|student,true');
Route::post('news','NewsController@store')->middleware('rbac.can:can-store|can-comment,true');
```