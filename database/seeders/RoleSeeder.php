<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    private $adminRole = [
        [
            'name' => 'absensi',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'cuti',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'karyawan_shift',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'shift',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'shift',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'user',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'gaji',
            'access' => [
                'create',
                'update',
                'delete',
                'read',
                'validate'
            ]
        ],
    ];

    private $karyawanRole = [
        [
            'name' => 'absensi',
            'access' => [
                'read'
            ]
        ],
        [
            'name' => 'cuti',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'gaji',
            'access' => [
                'read'
            ]
        ],
        [
            'name' => 'karyawan',
            'access' => [
                'update',
                'read'
            ]
        ],
        [
            'name' => 'karyawan_shift',
            'access' => [
                'read'
            ]
        ],
        [
            'name' => 'user',
            'access' => [
                'update',
                'read'
            ]
        ],
    ];

    public function run(): void
    {
        $allPermissions = [];

        foreach ($this->adminRole as $module) {
            foreach ($module['access'] as $access) {
                $permissionName = "{$access}_{$module['name']}";
                $allPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        foreach ($this->karyawanRole as $module) {
            foreach ($module['access'] as $access) {
                $permissionName = "{$access}_{$module['name']}";
                $allPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // Buat role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $karyawanRole = Role::firstOrCreate(['name' => 'karyawan']);

        $adminPermissions = array_unique(array_merge(
            array_map(function ($module) {
                return array_map(fn ($access) => "{$access}_{$module['name']}", $module['access']);
            }, $this->adminRole),
        ), SORT_REGULAR);
        
        $adminRole->syncPermissions($adminPermissions);

        $karyawanPermissions = array_unique(array_merge(
            array_map(function ($module) {
                return array_map(fn ($access) => "{$access}_{$module['name']}", $module['access']);
            }, $this->karyawanRole)
        ), SORT_REGULAR);

        $karyawanRole->syncPermissions($karyawanPermissions);

        $adminUser = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->syncRoles(['admin']);

        $karyawans = Karyawan::get();
        foreach ($karyawans as $karyawan) {
            $user = User::find($karyawan->user_id);
            if ($user) {
                $user->syncRoles(['karyawan']);
            }
        }
    }
}
