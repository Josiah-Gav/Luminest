<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HouseSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. Temporarily disable foreign key checks to avoid TRUNCATE foreign key constraint errors
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        
        // 2. Clear existing records in house table
        $this->table('house')->truncate();

        // 3. Define block configuration
        $blocksConfig = [
            1  => ['max_lot' => 20,  'type' => 'Angelique_Duplex'],
            2  => ['max_lot' => 10,  'type' => 'Armina_Single'],
            3  => ['max_lot' => 20,  'type' => 'Armina_Single'],
            4  => ['max_lot' => 30,  'type' => 'Armina_Duplex'],
            5  => ['max_lot' => 80,  'type' => 'Armina_Single'],
            6  => ['max_lot' => 80,  'type' => 'Armina_Single'],
            7  => ['max_lot' => 80,  'type' => 'Armina_Single'],
            8  => ['max_lot' => 20,  'type' => 'Angelique_Duplex'],
            9  => ['max_lot' => 50,  'type' => 'Aimee'],
            10 => ['max_lot' => 80,  'type' => 'Aimee'],
            11 => ['max_lot' => 100, 'type' => 'Aimee'],
            12 => ['max_lot' => 100, 'type' => 'Aimee'],
            13 => ['max_lot' => 100, 'type' => 'Aimee'],
            14 => ['max_lot' => 60,  'type' => 'Aimee'],
            15 => ['max_lot' => 20,  'type' => 'Aimee'],
        ];

        $data = [];

        // 4. Generate batch data for houses
        foreach ($blocksConfig as $block => $config) {
            for ($lot = 1; $lot <= $config['max_lot']; $lot++) {
                $data[] = [
                    'house_type' => $config['type'],
                    'lot'        => $lot,
                    'block'      => $block,
                    'status'     => 'available',
                    'owner_id'   => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        // 5. Insert data and re-enable foreign key checks
        $this->table('house')->insert($data)->saveData();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
    }
}