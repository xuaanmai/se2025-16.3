<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            'permission', 'project', 'project status', 'role', 'ticket',
            'ticket priority', 'ticket status', 'ticket type', 'user',
            'activity', 'sprint'
        ];

        $actions = ['list', 'view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            $plural = Str::plural($module);
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $action . ' ' . $plural]);
            }
        }

        $extraPermissions = [
            'manage general settings', 'import from jira',
            'export timesheet', 'view timesheet dashboard'
        ];

        foreach ($extraPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and their permissions
        $roles = [
            'Admin' => Permission::all()->pluck('name')->toArray(),
            'Manager' => [
                'list projects', 'view projects', 'create projects', 'update projects',
                'list tickets', 'view tickets', 'create tickets', 'update tickets', 'delete tickets',
                'list users', 'view users',
                'list sprints', 'view sprints', 'create sprints', 'update sprints', 'delete sprints',
                'view timesheet dashboard', 'export timesheet'
            ],
            'Developer' => [
                'list projects', 'view projects',
                'list tickets', 'view tickets', 'create tickets', 'update tickets',
                'list sprints', 'view sprints',
            ],
            'Customer' => [
                'view projects',
                'list tickets', 'view tickets', 'create tickets',
            ]
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }

        // Set default role in settings
        $defaultRole = Role::where('name', 'Developer')->first();
        if ($defaultRole) {
            $settings = app(GeneralSettings::class);
            $settings->default_role = $defaultRole->id;
            $settings->save();
        }

        // Assign Admin role to the main admin user
        $adminUser = User::where('email', 'john.doe@helper.app')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['Admin']);
        }
    }
}
