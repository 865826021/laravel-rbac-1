<?php

use App\Models\User;
use DmitryBubyakin\RBAC\Models\Permission;
use DmitryBubyakin\RBAC\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RBACTest extends TestCase
{
    use DatabaseTransactions;


    public function setUp()
    {
        parent::setUp();
        config(['rbac.cache.enabled' => false]);
    }

    /**
     * Role attach\detach test
     *
     * @return void
     */
    public function testRole()
    {
        /** @var $user App\Models\User */
        factory(User::class);
        $user = User::first();
        $user->attachRole('admin');
        $role = $user->roles()->first();
        $this->assertTrue($role->name === 'admin');
        $this->assertCount(1, $user->roles()->get());
        $user->detachRole($role);
        $this->assertCount(0, $user->roles()->get());
        $user->attachRole(['admin', 'user', 'student']);
        $this->assertCount(3, $user->roles()->get());
        $this->assertTrue($user->roleIs('admin'));
        $this->assertTrue($user->roleIs(['admin', 'user', 'student']));
        $this->assertTrue($user->roleIs(['admin', 'user', Role::where('name', 'student')->first()]));
        $this->assertTrue($user->roleIs(['admin', 'user', 'guest'], false));
        $this->assertFalse($user->roleIs(['admin', 'user', 'guest']));
        $user->detachRole('admin');
        $this->assertCount(2, $user->roles()->get());
        $this->assertFalse($user->roleIs('admin'));

    }

    public function testPermission()
    {
        /** @var $user User */
        factory(User::class);
        $user = User::first();
        $user->attachRole(['admin', 'user', 'student']);
        $roles = $user->roles()->get();

        $adminRole   = $roles->where('name', 'admin')->first();
        $userRole    = $roles->where('name', 'user')->first();
        $studentRole = $roles->where('name', 'student')->first();

        $adminRole->attachPermission(['post', 'update', 'delete', 'comment', 'create']);
        $userRole->attachPermission(['fill', 'send', 'test']);
        $studentRole->attachPermission('sleep');
        $studentRole->attachPermission(Permission::create(['name' => 'watch']));

        $this->assertTrue($adminRole->hasPermission(['post', 'update', 'delete', 'comment', 'create']));
        $this->assertTrue($adminRole->hasPermission(['post', Permission::where('name', 'create')->first()]));

        $this->assertTrue($user->can('post'));
        $this->assertTrue($user->can('fill'));
        $this->assertTrue($user->can('watch'));


        $this->assertTrue($user->can(['fill', 'post', 'sleep']));
        $this->assertTrue($user->can(['fill', 'wake up'], false));
        $this->assertTrue($user->can(['wake up', 'fill'], false));

        $studentRole->detachPermission('watch');

        $userRole->permissions()->sync([]);

        $this->assertFalse($user->can('watch'));
        $this->assertFalse($user->can(['fill', 'send', 'test']));
        $this->assertFalse($user->can(['fill', 'send', 'comment']));

        $this->assertTrue($user->can(['fill', 'send', 'comment'], false));

    }
}
