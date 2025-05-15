<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat semua permission utama
        $permissions = [
            'view-living-cost',
            'create-living-cost',
            'edit-living-cost',
            'delete-living-cost',
            'create-city',
            'edit-city',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        // Buat role admin jika belum ada
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // Berikan semua permission ke admin
        $admin->givePermissionTo($permissions);

        // (Opsional) Buat role user biasa dengan izin terbatas
        // $user = Role::firstOrCreate(['name' => 'user']);
        // $user->givePermissionTo(['view-living-cost']);
    }
}
