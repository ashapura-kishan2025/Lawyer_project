<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $modules = [
            'ASSIGNMENT' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'CLIENTS' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'QUOTATIONS' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'CURRENCY' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'DEPARTMENT' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'COUNTRY' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
            'USER' => ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'],
        ]; 
        foreach ($modules as $module => $permissions) {
            foreach ($permissions as $permission) {
                $permissionName = $module . '.' . $permission;
                
                try {
                    // Check if the permission already exists
                    if (!Permission::where('name', $permissionName)->exists()) {
                        Permission::create(['name' => $permissionName]);
                        $this->command->info("Permission '$permissionName' created.");
                    } else {
                        $this->command->info("Permission '$permissionName' already exists.");
                    }
                } catch (PermissionAlreadyExists $e) {
                    // Handle exception for already existing permission
                    $this->command->info("Permission '$permissionName' already exists.");
                } catch (\Exception $e) {
                    // Handle any other exceptions that may occur
                    $this->command->error("Error creating permission '$permissionName': " . $e->getMessage());
                }
            }
        }
    }
}
