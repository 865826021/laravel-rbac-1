<?php

use App\Models\User;
use DmitryBubyakin\Rbac\Models\Permission;
use DmitryBubyakin\Rbac\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RbacTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * @var User
     */
    protected $user = null;
    protected $roles = null;
    protected $permissions = null;


    protected function setUpModels()
    {
        $this->user = User::create([
            'email' => 'test@test.domain',
            'password' => 'password',
            'name' => 'user',
        ]);

        $this->roles = [
            'admin' => Role::create(['name' => 'admin']),
            'user' => Role::create(['name' => 'user']),
        ];

        $this->permissions = [
            'can-post' => Permission::create(['name' => 'can-post']),
            'can-comment' => Permission::create(['name' => 'can-comment']),
            'can-update' => Permission::create(['name' => 'can-update']),
            'can-delete' => Permission::create(['name' => 'can-delete']),
        ];

    }

    protected function setUpRoutes()
    {
        Route::get('rbac/guest', function () {
            return 'guest';
        })->name('rbac.guest');

        Route::get('rbac/admin', function () {
            return 'admin';
        })->middleware('rbac.role:admin');

        Route::get('rbac/adminuser', function () {
            return 'admin';
        })->middleware('rbac.role:admin|user');//admin and user

        Route::get('rbac/admin-user', function () {
            return 'admin';
        })->middleware('rbac.role:admin|user,false');//admin or user

        Route::get('rbac/admin-post-delete', function () {
            return 'ok';
        })->middleware(['rbac.can:can-post|can-delete,false', 'rbac.role:admin']);//can-post or can-delete with admin

        Route::get('rbac/admin-postdelete', function () {
            return 'ok';
        })->middleware(['rbac.can:can-post|can-delete', 'rbac.role:admin']);//can-post and can-delete with admin
    }

    public function setUp()
    {
        parent::setUp();
        config(['rbac.cache.enabled' => false]);

        $this->setUpModels();
        $this->setUpRoutes();

    }

    public function testMiddleware()
    {
        $this->assertEquals(200, $this->call('GET', 'rbac/guest')->status());
        $this->assertEquals(403, $this->call('GET', 'rbac/admin')->status());

        $this->actingAs($this->user);

        $this->assertEquals(200, $this->call('GET', 'rbac/guest')->status());
        $this->assertEquals(403, $this->call('GET', 'rbac/admin')->status());

        $this->user->attachRole($this->roles['admin']);
        $this->assertEquals(200, $this->call('GET', 'rbac/admin')->status());
        $this->assertEquals(200, $this->call('GET', 'rbac/admin-user')->status());//admin or user
        $this->assertEquals(403, $this->call('GET', 'rbac/adminuser')->status());//admin and user

        $this->user->attachRole($this->roles['user']);
        $this->assertEquals(200, $this->call('GET', 'rbac/adminuser')->status());

        $this->roles['admin']->attachPermission($this->permissions['can-post']);
        $this->assertEquals(200, $this->call('GET', 'rbac/admin-post-delete')->status());
        $this->assertEquals(403, $this->call('GET', 'rbac/admin-postdelete')->status());

        $this->roles['user']->attachPermission($this->permissions['can-delete']);
        $this->assertEquals(200, $this->call('GET', 'rbac/admin-postdelete')->status());
    }

    /**
     * Role attach\detach test
     *
     * @return void
     */
    public function testRole()
    {
        $this->actingAs($this->user);

        $this->assertEquals(0, $this->user->getRoles()->count());

        $this->user->attachRole('admin');
        $this->assertEquals(1, $this->user->getRoles()->count());

        $this->assertTrue($this->user->roleIs('admin'));
        $this->assertTrue($this->user->roleIs('admin|user', false));//admin or user
        $this->assertTrue($this->user->roleIs(['admin', 'user'], false));//admin or user

        $this->assertFalse($this->user->roleIs('admin|user'));//admin and user
        $this->assertFalse($this->user->roleIs(['admin', 'user']));//admin and user

        $this->user->detachRole('admin');
        $this->assertEquals(0, $this->user->getRoles()->count());

        $this->assertFalse($this->user->roleIs('admin'));
        $this->assertFalse($this->user->roleIs('admin|user'));
        $this->assertFalse($this->user->roleIs('admin|user', false));

        $this->user->attachRole(['admin', 'user']);
        $this->assertEquals(2, $this->user->getRoles()->count());

        $this->assertTrue($this->user->roleIs('admin'));
        $this->assertTrue($this->user->roleIs('admin|user', false));//admin or user
        $this->assertTrue($this->user->roleIs(['admin', 'user'], false));//admin or user

        $this->assertTrue($this->user->roleIs('admin|user'));//admin and user
        $this->assertTrue($this->user->roleIs(['admin', 'user']));//admin and user

        $this->user->detachRole(['admin', 'user']);
        $this->assertEquals(0, $this->user->getRoles()->count());
    }

    public function testPermission()
    {
        $this->actingAs($this->user);

        $admin = $this->roles['admin'];

        $this->assertFalse($admin->hasPermission('can-post'));
        $this->assertFalse($admin->hasPermission(['can-post', 'can-delete']));
        $this->assertFalse($admin->hasPermission(['can-post', 'can-delete'], false));

        $admin->attachPermission('can-post');
        $this->assertEquals(1, $admin->getPermissions()->count());

        $this->assertTrue($admin->hasPermission('can-post'));
        $this->assertFalse($admin->hasPermission(['can-post', 'can-delete']));
        $this->assertTrue($admin->hasPermission(['can-post', 'can-delete'], false));

        $admin->attachPermission('can-delete');
        $this->assertEquals(2, $admin->getPermissions()->count());

        $this->assertTrue($admin->hasPermission('can-post'));
        $this->assertTrue($admin->hasPermission(['can-post', 'can-delete']));
        $this->assertTrue($admin->hasPermission(['can-post', 'can-delete'], false));

        $admin->detachPermission('can-post');
        $this->assertEquals(1, $admin->getPermissions()->count());

        $this->assertFalse($admin->hasPermission('can-post'));
        $this->assertFalse($admin->hasPermission(['can-post', 'can-delete']));
        $this->assertTrue($admin->hasPermission(['can-post', 'can-delete'], false));


        $admin->attachPermission(['can-post','1','2','3','4','5']);
        $this->assertEquals(7, $admin->getPermissions()->count());
        $admin->detachPermission(['3','4','5']);
        $this->assertEquals(4, $admin->getPermissions()->count());
        $admin->detachPermission();//detach all
        $this->assertEquals(0, $admin->getPermissions()->count());
    }
}
