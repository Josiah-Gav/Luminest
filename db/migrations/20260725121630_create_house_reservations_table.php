<?php

use Phinx\Migration\AbstractMigration;

class CreateHouseReservationsTable extends AbstractMigration
{
    public function change(): void
    {
        // Custom primary key for house_reservations
        $table = $this->table('house_reservations', ['id' => 'reservation_id']);

        $table
            // Match users.user_id data type exactly: INT(11) UNSIGNED
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'References user_id on users table'
            ])
            ->addColumn('house_type', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('block', 'integer', [
                'null' => false
            ])
            ->addColumn('lot', 'integer', [
                'null' => false
            ])
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'cancelled', 'rejected', 'accepted', 'completed'],
                'default' => 'pending',
                'null' => false
            ])
            ->addTimestamps()
            // Link local user_id -> users.user_id
            ->addForeignKey('user_id', 'users', 'user_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}