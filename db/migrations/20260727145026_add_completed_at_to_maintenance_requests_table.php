<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddCompletedAtToMaintenanceRequestsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        $table = $this->table('maintenance_requests');

        $table->addColumn('completed_at', 'datetime', [
            'null'    => true,
            'default' => null,
            'after'   => 'status', // Positions the column right after 'status'
        ])
        ->update();
    }
}