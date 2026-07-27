<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Disable foreign key checks to safely truncate
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->table('users')->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');

        // Default password for all seed accounts
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

        $data = [
            [
                'full_name'     => 'Prospect User',
                'email'         => 'prospect@luminest.com',
                'password_hash' => $passwordHash,
                'role'          => 'Prospect',
            ],
            [
                'full_name'     => 'Tenant User',
                'email'         => 'tenant@luminest.com',
                'password_hash' => $passwordHash,
                'role'          => 'Tenant',
            ],
            [
                'full_name'     => 'Maintenance Staff',
                'email'         => 'staff@luminest.com',
                'password_hash' => $passwordHash,
                'role'          => 'Maintenance_Staff',
            ],
            [
                'full_name'     => 'Property Manager',
                'email'         => 'manager@luminest.com',
                'password_hash' => $passwordHash,
                'role'          => 'Property_Manager',
            ],
            [
                'full_name'     => 'Admin User',
                'email'         => 'admin@luminest.com',
                'password_hash' => $passwordHash,
                'role'          => 'Admin',
            ],
        ];

        $users = $this->table('users');
        $users->insert($data)
              ->saveData();
    }
}