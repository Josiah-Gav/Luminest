<?php

use Phinx\Migration\AbstractMigration;

class AddPaidStatusToHouseReservationsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Updates the status ENUM column to include 'paid'.
     */
    public function change(): void
    {
        $table = $this->table('house_reservations');

        // Update the ENUM values by calling changeColumn
        $table->changeColumn('status', 'enum', [
            'values' => ['pending', 'cancelled', 'rejected', 'accepted', 'completed', 'paid'],
            'default' => 'pending',
            'null' => false
        ])
        ->update();
    }
}