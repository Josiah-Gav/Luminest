<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateUserTable extends AbstractMigration
{
    public function change(): void
    {
        // By default, Phinx names the primary key 'id'.
        // Setting 'id' => 'user_id' matches your exact SQL schema.
        $table = $this->table('users', ['id' => 'user_id']);

        $table->addColumn('full_name', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('email', 'string', ['limit' => 150, 'null' => false])
              ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('phone_number', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('role', 'enum', [
                  'values' => ['Admin', 'Prospect', 'Property_Manager', 'Tenant', 'Maintenance_Staff'],
                  'null' => false
              ])
              ->addIndex(['email'], ['unique' => true])
              ->addTimestamps() // Automatically adds created_at and updated_at with MySQL defaults
              ->create();
    }
}