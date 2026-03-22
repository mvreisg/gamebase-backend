<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Authorization\Types\SectorTypes;
use Phinx\Seed\AbstractSeed;

class AddingSectors extends AbstractSeed
{
    public function run(): void
    {
        $data = [];
        foreach (SectorTypes::cases() as $key => $value) {
            $result = $this->fetchRow(
                "SELECT COUNT(*) AS count FROM sector WHERE name = '{$value->name}'",
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
                ->table("sector")
                ->insert($data)
                ->saveData();
        }
    }
}
