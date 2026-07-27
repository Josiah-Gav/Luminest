<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddUniqueIndexToUsersPhoneNumber extends AbstractMigration
{
    public function up(): void
    {
        // Empty strings should behave like missing numbers so the unique index allows multiple NULL values.
        $this->execute("UPDATE users SET phone_number = NULL WHERE phone_number = ''");

        $rows = $this->fetchAll(
            "SELECT user_id, phone_number
             FROM users
             WHERE phone_number IS NOT NULL"
        );

        $normalizedPhoneToUser = [];

        foreach ($rows as $row) {
            $normalizedPhone = preg_replace('/\\D+/', '', (string)$row['phone_number']);

            if ($normalizedPhone === '') {
                $this->execute(
                    sprintf(
                        "UPDATE users SET phone_number = NULL WHERE user_id = %d",
                        (int)$row['user_id']
                    )
                );
                continue;
            }

            if (isset($normalizedPhoneToUser[$normalizedPhone])) {
                throw new RuntimeException(
                    'Cannot add unique index on users.phone_number because duplicate phone numbers exist after normalization. Conflicting user IDs: '
                    . $normalizedPhoneToUser[$normalizedPhone]
                    . ' and '
                    . (int)$row['user_id']
                    . '.'
                );
            }

            $normalizedPhoneToUser[$normalizedPhone] = (int)$row['user_id'];

            if ($normalizedPhone !== $row['phone_number']) {
                $this->execute(
                    sprintf(
                        "UPDATE users SET phone_number = '%s' WHERE user_id = %d",
                        addslashes($normalizedPhone),
                        (int)$row['user_id']
                    )
                );
            }
        }

        $table = $this->table('users');
        $table->addIndex(['phone_number'], ['unique' => true, 'name' => 'idx_users_phone_number_unique'])
              ->update();
    }

    public function down(): void
    {
        $table = $this->table('users');
        $table->removeIndexByName('idx_users_phone_number_unique')
              ->update();
    }
}
