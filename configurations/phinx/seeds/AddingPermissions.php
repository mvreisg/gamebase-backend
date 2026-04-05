<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Phinx\Seed\AbstractSeed;

class AddingPermissions extends AbstractSeed
{
    public function run(): void
    {
        $data = [];
        foreach (PermissionType::cases() as $key => $value) {
            $result = $this->fetchRow(
                "SELECT COUNT(*) AS count FROM permission WHERE name = '{$value->name}'",
            );

            if ($result["count"] > 0) {
                continue;
            }

            $data[] = [
                "name" => $value->name,
                "value" => $value->value,
                "is_active" => 1
            ];
        }

        if (count($data) > 0) {
            $this
                ->table("permission")
                ->insert($data)
                ->saveData();
        }
    }
}
