<?php

class House
{
    public const DEFAULT_SLUG = 'aimee';

    private $conn;

    private static $houseTypes = [
        'aimee' => [
            'slug' => 'aimee',
            'title' => 'Aimee Rowhouse',
            'type' => 'One-Storey Rowhouse',
            'db_type' => 'Aimee',
            'image' => '/luminest/assets/Lumina(Aimee).jpg',
            'description' => 'Compact, practical, and easy to maintain.',
            'bedrooms' => '1',
            'bathrooms' => '1',
            'carports' => '0',
            'badge' => 'Starter home',
        ],
        'angelique_duplex' => [
            'slug' => 'angelique_duplex',
            'title' => 'Angelique Duplex',
            'type' => 'Two-Storey Duplex',
            'db_type' => 'Angelique_Duplex',
            'image' => '/luminest/assets/Lumina(Angelique).jpg',
            'description' => 'A balanced layout with a quiet upstairs zone.',
            'bedrooms' => '2',
            'bathrooms' => '1',
            'carports' => '1',
            'badge' => 'Family ready',
        ],
        'armina_single' => [
            'slug' => 'armina_single',
            'title' => 'Armina Single',
            'type' => 'Two-Storey Single',
            'db_type' => 'Armina_Single',
            'image' => '/luminest/assets/Lumina(Armina_single).jpg',
            'description' => 'More room for growing households and flexible living.',
            'bedrooms' => '3',
            'bathrooms' => '1',
            'carports' => '1',
            'badge' => 'Room to grow',
        ],
        'armina_duplex' => [
            'slug' => 'armina_duplex',
            'title' => 'Armina Duplex',
            'type' => 'Two-Storey Duplex',
            'db_type' => 'Armina_Duplex',
            'image' => '/luminest/assets/Lumina(Armina).jpg',
            'description' => 'A generous duplex plan with a modern, functional flow.',
            'bedrooms' => '3',
            'bathrooms' => '1',
            'carports' => '1',
            'badge' => 'Spacious choice',
        ],
    ];

    public function __construct($db = null)
    {
        $this->conn = $db;
    }

    public function all(): array
    {
        return array_values(self::$houseTypes);
    }

    public function getBySlug(string $slug): array
    {
        if (!isset(self::$houseTypes[$slug])) {
            return self::$houseTypes[self::DEFAULT_SLUG];
        }

        return self::$houseTypes[$slug];
    }

    public function exists(string $slug): bool
    {
        return isset(self::$houseTypes[$slug]);
    }

    public function getByDbType(string $dbType): ?array
    {
        foreach (self::$houseTypes as $houseType) {
            if (($houseType['db_type'] ?? '') === $dbType) {
                return $houseType;
            }
        }

        return null;
    }

    public function getAvailableBlocks(string $dbType): array
    {
        if (!$this->conn) {
            return [];
        }

        $stmt = $this->conn->prepare(
            'SELECT DISTINCT block FROM house WHERE house_type = :house_type AND status = :status ORDER BY block ASC'
        );
        $stmt->execute([
            ':house_type' => $dbType,
            ':status' => 'available',
        ]);

        $blocks = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_map('intval', $blocks);
    }

    public function getAvailableLots(string $dbType, int $block): array
    {
        if (!$this->conn || $block <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare(
            'SELECT lot FROM house WHERE house_type = :house_type AND block = :block AND status = :status ORDER BY lot ASC'
        );
        $stmt->execute([
            ':house_type' => $dbType,
            ':block' => $block,
            ':status' => 'available',
        ]);

        $lots = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_map('intval', $lots);
    }

    public function getHouseByTypeBlockLot(string $dbType, int $block, int $lot): ?array
    {
        if (!$this->conn || $block <= 0 || $lot <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            'SELECT house_id, house_type, lot, block, status, date_of_purchase, owner_id, created_at, updated_at
             FROM house
             WHERE house_type = :house_type AND block = :block AND lot = :lot
             LIMIT 1'
        );
        $stmt->execute([
            ':house_type' => $dbType,
            ':block' => $block,
            ':lot' => $lot,
        ]);

        $house = $stmt->fetch(PDO::FETCH_ASSOC);

        return $house ?: null;
    }

    public function markAsReserved(int $houseId): bool
    {
        if (!$this->conn || $houseId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'UPDATE house
             SET status = :status, owner_id = NULL, date_of_purchase = NULL, updated_at = NOW()
             WHERE house_id = :house_id AND status = :expected_status'
        );

        $stmt->execute([
            ':status' => 'reserved',
            ':house_id' => $houseId,
            ':expected_status' => 'available',
        ]);

        return $stmt->rowCount() === 1;
    }

    public function markAsAvailableByUnit(string $houseType, int $block, int $lot): bool
    {
        if (!$this->conn || $houseType === '' || $block <= 0 || $lot <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'UPDATE house
             SET status = :available_status, owner_id = NULL, date_of_purchase = NULL, updated_at = NOW()
             WHERE house_type = :house_type
               AND block = :block
               AND lot = :lot
               AND status = :reserved_status'
        );

        $stmt->execute([
            ':available_status' => 'available',
            ':house_type' => $houseType,
            ':block' => $block,
            ':lot' => $lot,
            ':reserved_status' => 'reserved',
        ]);

        return $stmt->rowCount() === 1;
    }
}