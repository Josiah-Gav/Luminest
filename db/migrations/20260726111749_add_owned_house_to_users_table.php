<?php

use Phinx\Migration\AbstractMigration;

class AddOwnedHouseToUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('owned_house', 'integer', [
            'signed'   => false,
            'null'     => true, // Nullable since a user might not own a house yet
            'after'    => 'role', // Optional: positions the column after 'role'
            'comment'  => 'Foreign key referencing house.house_id'
        ])
        ->addForeignKey('owned_house', 'house', 'house_id', [
            'delete' => 'SET_NULL',
            'update' => 'CASCADE',
            'constraint' => 'fk_users_owned_house'
        ])
        ->update();
    }
}