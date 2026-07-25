<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateHouseTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        $table = $this->table('house', ['id' => 'house_id']);
        
        $table->addColumn('house_type', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('lot', 'integer', ['null' => false])
              ->addColumn('block', 'integer', ['null' => false])
              ->addColumn('status', 'enum', [
                  'values' => ['available', 'sold', 'reserved'],
                  'default' => 'available',
                  'null' => false
              ])
              // Foreign key column referencing users.user_id
              ->addColumn('owner_id', 'integer', [
                  'signed' => false,
                  'null' => true // Set to true if a house can be unowned/available
              ])
              ->addForeignKey('owner_id', 'users', 'user_id', [
                  'delete' => 'SET_NULL',
                  'update' => 'CASCADE'
              ])
              ->addTimestamps()
              ->create();
    }
}