<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::truncate();
        $faker = Faker::create('id_ID');
        $users = [
            [
                'name' => 'Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'module' => $this->adminRole,
            ],
            [
                'name' => 'Karyawan',
                'email' => 'karyawan@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'module' => $this->karyawanRole,
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                $userData  = User::updateOrCreate([
                    'email' => $user['email']
                ],[
                    'name' => $user['name'],
                    'password' => $user['password'],
                ]);

                if ($user['role'] == 'karyawan') {
                    Karyawan::updateOrCreate(
                        ['user_id' => $userData->id],
                        [
                            'nama' => $user['name'],
                            'alamat' => $faker->address,
                            'no_telp' => '081234567890',
                            'tanggal_masuk' => now()->subMonths(6), 
                            'status_karyawan' => 'aktif',
                            'rfid_number' => null,
                            'saldo_cuti' => 2,
                            'is_active' => true,
                        ]
                    );
                }

                $role = Role::firstOrCreate([
                    'name' => $user['role']
                ]);

                foreach ($user['module'] as $key => $value){
                    $moduleName = $value['name'];
                    $permissions = $value['access'];

                    foreach ($permissions as $permission){
                        $permission = Permission::firstOrCreate([
                            'name' => $permission."_".$moduleName
                        ]);
                        $permission->assignRole($role);
                    }

                }

                $userData->assignRole($user['role']);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
        
    }

    private $adminRole = [
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
            'name' => 'karyawan',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'penggajian',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
        [
            'name' => 'pengajuan_cuti',
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
        ]
    ];

    private $karyawanRole = [
        [
            'name' => 'karyawan',
            'access' => [
                'create',
                'update',
                'delete',
                'read'
            ]
        ],
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
            'name' => 'penggajian',
            'access' => [
                'read'
            ]
        ],
        [
            'name' => 'pengajuan_cuti',
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
                'read'
            ]
        ]
    ];
}
