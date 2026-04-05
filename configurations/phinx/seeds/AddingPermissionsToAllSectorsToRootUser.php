<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Phinx\Seed\AbstractSeed;

class AddingPermissionsToAllSectorsToRootUser extends AbstractSeed
{
    public function run(): void
    {
        require_once dirname(__DIR__, 3) . "/constants.php";

        /**
         * @var Container
         */
        $container = require PROJECT_ROOT . "/configurations/php_di/phinx/container_bootstrap.php";

        $userResult = $this->fetchRow("SELECT * FROM user WHERE username = '{$container->get("repository.root.username")}'");

        $data = [];
        foreach (SectorType::cases() as $sectorKey => $sectorValue) {
            $sectorResult = $this->fetchRow("SELECT * FROM sector WHERE value = '{$sectorValue->value}'");

            foreach (PermissionType::cases() as $permissionKey => $permissionValue) {
                $mustIgnore = false;
                switch ($sectorValue) {
                    case SectorType::GameGenre:
                    case SectorType::GamePlatform:
                    case SectorType::UserSectorPermission:
                        if ($permissionValue === PermissionType::Activate) {
                            $mustIgnore = true;
                        }
                        break;
                    default:
                        if ($permissionValue === PermissionType::Delete) {
                            $mustIgnore = true;
                        }
                        break;
                }
                if ($mustIgnore) {
                    continue;
                }
                $permissionResult = $this->fetchRow("SELECT * FROM permission WHERE value = '{$permissionValue->value}'");

                $userSectorPermissionResult = $this->fetchRow(
                    "SELECT 
                        COUNT(*) 
                    AS 
                        user_sector_permission_count 
                    FROM 
                        user_sector_permission 
                    WHERE 
                        user_id = {$userResult["id"]}
                    AND
                        sector_id = {$sectorResult["id"]}
                    AND
                        permission_id = {$permissionResult["id"]}
                    ;"
                );

                if ($userSectorPermissionResult["user_sector_permission_count"] > 0) {
                    continue;
                }

                $data[] = [
                    "user_id" => $userResult["id"],
                    "sector_id" => $sectorResult["id"],
                    "permission_id" => $permissionResult["id"]
                ];
            }
        }

        if (count($data) > 0) {
            $this
                ->table("user_sector_permission")
                ->insert($data)
                ->saveData();
        }
    }
}
