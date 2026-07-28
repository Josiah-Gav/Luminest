<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatusToUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('status', 'enum', [
            'values'  => ['Active', 'Inactive'],
            'default' => 'Active',
            'null'    => false,
            'after'   => 'email', // Position of the column (optional)
        ])
        ->update();
    }
}