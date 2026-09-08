<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Phinx\Seed\AbstractSeed;
use DI\Container;
use Mvreisg\GamebaseBackend\Domain\Authorization\Service\AuthorizationDomainService;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;

class AddingUserSectorPermission extends AbstractSeed
{
    public function run(): void
    {
        require_once dirname(__DIR__, 3) . "/constants.php";

        /**
         * @var Container
         */
        $container = require PROJECT_ROOT . "/configurations/php_di/phinx/container_bootstrap.php";

        $userResult = $this->fetchRow("SELECT * FROM user WHERE username = '{$container->get("repository.root.username")}'");

        $authorizationDomainService = $container->get(AuthorizationDomainService::class);

        $data = [];
        foreach (SectorType::cases() as $sectorType) {
            $sectorResult = $this->fetchRow("SELECT * FROM sector WHERE value = '{$sectorType->value}'");

            foreach (PermissionType::cases() as $permissionType) {
                $permissionResult = $this->fetchRow("SELECT * FROM permission WHERE value = '{$permissionType->value}'");

                try {
                    $isAuthorized = $authorizationDomainService->check(SectorValue::from($sectorType), PermissionValue::from($permissionType));
                    if ($isAuthorized === false) {
                        continue;
                    }
                } catch (\Throwable) {
                    continue;
                }

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
