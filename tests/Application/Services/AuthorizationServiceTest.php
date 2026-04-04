<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Services;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Exception\EntityException;
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
use Mvreisg\GamebaseBackend\Domain\Repositories\Exception\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    private function createUser(
        Id $id,
        Username $username,
        DecodedPassword $password,
        bool $isActive
    ): User {
        $user = new User(
            $username,
            $password,
            $isActive
        );
        $user->setId($id);
        return $user;
    }

    private function createSector(
        Id $id,
        Name $name,
        SectorValue $value,
        bool $isActive,
    ): Sector {
        $sector = new Sector(
            $name,
            $value,
            $isActive
        );
        $sector->setId($id);
        return $sector;
    }

    private function createPermission(
        Id $id,
        Name $name,
        PermissionValue $value,
        bool $isActive,
    ): Permission {
        $permission = new Permission(
            $name,
            $value,
            $isActive
        );
        $permission->setId($id);
        return $permission;
    }

    private function createUserRepository(
        User $user
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("findById")
            ->willReturn(
                $user
            );
        return $userRepository;
    }

    private function createUserRepositoryWithUserExistanceNegation(
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->method("checkIfExists")
            ->willThrowException(
                new RepositoryUnexistantRegisterException("User not found.")
            );
        return $userRepository;
    }

    private function createPermissionRepository(
        Permission $permission
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface {
        $permissionRepository = $this->createMock(PermissionRepositoryInterface::class);
        $permissionRepository
            ->method("findById")
            ->willReturn(
                $permission
            );
        return $permissionRepository;
    }

    private function createSectorRepository(
        Sector $sector
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface {
        $sectorRepository = $this->createMock(SectorRepositoryInterface::class);
        $sectorRepository
            ->method("findById")
            ->willReturn(
                $sector
            );
        return $sectorRepository;
    }

    private function createUserSectorPermission(
        Id $id,
        Id $userId,
        Id $sectorId,
        Id $permissionId
    ): UserSectorPermission {
        $userSectorPermission = new UserSectorPermission(
            $userId,
            $sectorId,
            $permissionId
        );
        $userSectorPermission->setId($id);
        return $userSectorPermission;
    }

    private function createUserSectorPermissionRepository(
        UserSectorPermissionCollection $userSectorPermissionCollection
    ): MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface {
        $userSectorPermissionRepository = $this->createMock(UserSectorPermissionRepositoryInterface::class);
        $userSectorPermissionRepository
            ->method("findAllByUserId")
            ->willReturn(
                $userSectorPermissionCollection
            );
        return $userSectorPermissionRepository;
    }

    private function createAuthorizationService(
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface $userRepository,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\SectorRepositoryInterface $sectorRepository,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\PermissionRepositoryInterface $permissionRepository,
        MockObject&\Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserSectorPermissionRepositoryInterface $userSectorPermissionRepository
    ): AuthorizationService {
        return new AuthorizationService(
            $userRepository,
            $permissionRepository,
            $sectorRepository,
            $userSectorPermissionRepository
        );
    }

    /*
    ---------------
    | Check Tests |
    ---------------
    */

    public function testIfAUserHaveTheCorrectAuthorizations(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->createUser(
            Id::create(1),
            new Username("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            new Name("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            new Name("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user->getId(),
            $sector->getId(),
            $permission->getId()
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $sectorRepository = $this->createSectorRepository(
            $sector
        );
        $permissionRepository = $this->createPermissionRepository(
            $permission
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $authorizationService = $this->createAuthorizationService(
            $userRepository,
            $sectorRepository,
            $permissionRepository,
            $userSectorPermissionRepository
        );

        $authorizationService->check(
            Id::create(1),
            SectorTypes::User,
            PermissionTypes::Create
        );
    }

    public function testIfAUserDoNotHaveTheCorrectSectorAuthorization(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user->getId(),
            $sector->getId(),
            $permission->getId()
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $sectorRepository = $this->createSectorRepository(
            $sector
        );
        $permissionRepository = $this->createPermissionRepository(
            $permission
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $authorizationService = $this->createAuthorizationService(
            $userRepository,
            $sectorRepository,
            $permissionRepository,
            $userSectorPermissionRepository
        );

        $authorizationService->check(
            $user->getId(),
            SectorTypes::Game,
            PermissionTypes::Create
        );
    }

    public function testIfAUserDoNotHaveTheCorrectPermissionAuthorization(): void
    {
        $this->expectException(UnauthorizedException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user->getId(),
            $sector->getId(),
            $permission->getId()
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepository(
            $user
        );
        $sectorRepository = $this->createSectorRepository(
            $sector
        );
        $permissionRepository = $this->createPermissionRepository(
            $permission
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $authorizationService = $this->createAuthorizationService(
            $userRepository,
            $sectorRepository,
            $permissionRepository,
            $userSectorPermissionRepository
        );

        $authorizationService->check(
            $user->getId(),
            SectorTypes::User,
            PermissionTypes::List
        );
    }

    public function testIfAUnexistantUserIdTriesToCheckAuthorization(): void
    {
        $this->expectException(EntityException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user->getId(),
            $sector->getId(),
            $permission->getId()
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepositoryWithUserExistanceNegation(
            $user
        );
        $sectorRepository = $this->createSectorRepository(
            $sector
        );
        $permissionRepository = $this->createPermissionRepository(
            $permission
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $authorizationService = $this->createAuthorizationService(
            $userRepository,
            $sectorRepository,
            $permissionRepository,
            $userSectorPermissionRepository
        );

        $authorizationService->check(
            Id::create(-1),
            SectorTypes::User,
            PermissionTypes::List
        );
    }

    public function testIfAUnexistantUserTriesToCheckAuthorization(): void
    {
        $this->expectException(RepositoryUnexistantRegisterException::class);

        $user = $this->createUser(
            Id::create(1),
            Username::create("test"),
            new DecodedPassword("password"),
            true
        );
        $sector = $this->createSector(
            Id::create(1),
            Name::create("User"),
            SectorTypes::getValue(SectorTypes::User),
            true
        );
        $permission = $this->createPermission(
            Id::create(1),
            Name::create("Create"),
            PermissionTypes::getValue(PermissionTypes::Create),
            true
        );
        $userSectorPermission = $this->createUserSectorPermission(
            Id::create(1),
            $user->getId(),
            $sector->getId(),
            $permission->getId()
        );
        $userSectorPermissionCollection = new UserSectorPermissionCollection([
            $userSectorPermission
        ]);

        $userRepository = $this->createUserRepositoryWithUserExistanceNegation(
            $user
        );
        $sectorRepository = $this->createSectorRepository(
            $sector
        );
        $permissionRepository = $this->createPermissionRepository(
            $permission
        );
        $userSectorPermissionRepository = $this->createUserSectorPermissionRepository(
            $userSectorPermissionCollection
        );
        $authorizationService = $this->createAuthorizationService(
            $userRepository,
            $sectorRepository,
            $permissionRepository,
            $userSectorPermissionRepository
        );

        $authorizationService->check(
            Id::create(2),
            SectorTypes::User,
            PermissionTypes::List
        );
    }
}
