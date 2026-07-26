<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddDateOfPurchaseToHouseTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('house');

        // Adds date_of_purchase column (nullable, placed after status)
        $table->addColumn('date_of_purchase', 'datetime', [
            'null'    => true,
            'default' => null,
            'after'   => 'status',
            'comment' => 'Records the date and time when the house was purchased/sold'
        ])
        ->update();
    }
}