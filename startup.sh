#!/bin/bash
./vendor/bin/phinx migrate -e development
./vendor/bin/phinx seed:run -e development -s AddingFirstUser
./vendor/bin/phinx seed:run -e development -s AddingSectors
./vendor/bin/phinx seed:run -e development -s AddingPermissions
./vendor/bin/phinx seed:run -e development -s AddingPermissionsToAllSectorsToRootUser