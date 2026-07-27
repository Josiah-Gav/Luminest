<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddResolvedToStatusInMaintenanceRequestsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        $table = $this->table('maintenance_requests');

        // Update the 'status' ENUM column to include 'resolved'
        $table->changeColumn('status', 'enum', [
            'values'  => [
                'pending',
                'accepted',
                'in-progress',
                'completed',
                'resolved',
                'cancelled',
                'rejected',
                'on-hold'
            ],
            'default' => 'pending',
            'null'    => false,
        ])
        ->update();
    }
}