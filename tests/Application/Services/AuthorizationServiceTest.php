<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Entities\Sector;
use Mvreisg\GamebaseBackend\Domain\Entities\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermissionCollection;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    private function createUserRepository(): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = new User(
            Username::make("marcus"),
            DecodedPassword::make("batata"),
            true
        );
        $user->setId(Id::make(1));
        $userRepository
            ->method("findById")
            ->willReturn(
                $user
            );
        return $userRepository;
    }

    private function createPermissionRepository(): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface
    {
        $permissionRepository = $this->createMock(PermissionRepositoryInterface::class);
        $permission = new Permission(
            Name::make("Create"),
            PermissionValue::make(PermissionTypes::Create->value),
            true
        );
        $permission->setId(Id::make(1));
        $permissionRepository
            ->method("findById")
            ->willReturn(
                $permission
            );
        return $permissionRepository;
    }

    private function createSectorRepository(): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface
    {
        $sectorRepository = $this->createMock(SectorRepositoryInterface::class);
        $sector = new Sector(
            Name::make("User"),
            SectorValue::make(SectorTypes::User->value),
            true
        );
        $sector->setId(Id::make(1));
        $sectorRepository
            ->method("findById")
            ->willReturn(
                $sector
            );
        return $sectorRepository;
    }

    private function createUserSectorPermissionRepository(): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface
    {
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn(
                new UserSectorPermissionCollection([
                    new UserSectorPermission(
                        Id::make(1),
                        Id::make(1),
                        Id::make(1),
                    )
                ])
            );
        return $userSectorPermissionRepository;
    }

    private function createAuthorizationService(): AuthorizationService
    {
        $userRepository = $this->createUserRepository();
        $permissionRepository = $this->createPermissionRepository();
        $sectorRepository = $this->createSectorRepository();
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository();
        return new AuthorizationService(
            $userRepository,
            $permissionRepository,
            $sectorRepository,
            $userSectorPermissionRepository
        );
    }

    public function testIfAUserHaveTheCorrectAuthorizations(): void
    {
        $authorizationService = $this->createAuthorizationService();

        $this->expectNotToPerformAssertions();

        $authorizationService->check(
            Id::make(1),
            SectorTypes::User,
            PermissionTypes::Create
        );
    }

    public function testIfAUserDoNotHaveTheCorrectAuthorizations(): void
    {
        $authorizationService = $this->createAuthorizationService();

        $this->expectException(UnauthorizedException::class);

        $authorizationService->check(
            Id::make(1),
            SectorTypes::Platform,
            PermissionTypes::Create
        );
    }

    public function testIfAUserWithAInvalidIdTriesToCheckAuthorization(): void
    {
        $authorizationService = $this->createAuthorizationService();

        $this->expectException(EntityException::class);

        $authorizationService->check(
            Id::make(-1),
            SectorTypes::Platform,
            PermissionTypes::Create
        );
    }
}
