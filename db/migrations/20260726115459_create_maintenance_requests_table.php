<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateMaintenanceRequestsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('maintenance_requests');
        $table->addColumn('tenant_id', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'References users.user_id'
            ])
            ->addColumn('house_id', 'integer', [
                'signed' => false,
                'null' => true,
                'comment' => 'References house.house_id'
            ])
            ->addColumn('title', 'string', [
                'limit' => 150,
                'null' => false,
                'comment' => 'Short summary of the issue'
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'comment' => 'Detailed description of the issue'
            ])
            ->addColumn('category', 'enum', [
                'values' => ['plumbing', 'electrical', 'carpentry', 'appliance', 'general'],
                'default' => 'general',
                'null' => false
            ])
            ->addColumn('priority', 'enum', [
                'values' => ['low', 'medium', 'high', 'urgent'],
                'default' => 'medium',
                'null' => false
            ])
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'accepted', 'in-progress', 'completed', 'cancelled', 'rejected'],
                'default' => 'pending',
                'null' => false
            ])
            ->addColumn('assigned_staff', 'integer', [
                'signed' => false,
                'null' => true,
                'comment' => 'References users.user_id'
            ])
            ->addColumn('resolution_notes', 'text', [
                'null' => true
            ])
            ->addColumn('resolved_at', 'timestamp', [
                'null' => true
            ])
            ->addTimestamps()
            
            // Foreign Keys
            ->addForeignKey('tenant_id', 'users', 'user_id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('house_id', 'house', 'house_id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('assigned_staff', 'users', 'user_id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION'
            ])
            
            // Indexes for faster lookups
            ->addIndex(['tenant_id'])
            ->addIndex(['house_id'])
            ->addIndex(['status'])
            ->addIndex(['priority'])
            ->create();
    }
}