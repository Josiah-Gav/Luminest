<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddExpertiseToUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('expertise', 'enum', [
            'values' => ['plumbing', 'electrical', 'carpentry', 'appliance', 'general'],
            'null'   => true,
            'default' => null,
            'after'  => 'role', // Places the column after 'role' (optional)
            'comment' => 'Specialization for maintenance staff users'
        ])
        ->update();
    }
}