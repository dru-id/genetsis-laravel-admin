<?php namespace Genetsis\Admin\Database\Seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_superadmin = \Spatie\Permission\Models\Role::create(['name' => 'SuperAdmin']);
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);

        Permission::create(['name' => 'manage_users']);

        $role_superadmin->givePermissionTo('manage_users');
    }
}
