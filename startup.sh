#!/bin/bash
composer phinx -- migrate -e development
composer phinx -- seed:run -e development -s AddingFirstUser
composer phinx -- seed:run -e development -s AddingSectors
composer phinx -- seed:run -e development -s AddingPermissions
composer phinx -- seed:run -e development -s AddingPermissionsToAllSectorsToRootUser