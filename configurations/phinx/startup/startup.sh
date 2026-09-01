#!/usr/bin/env bash

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../" && pwd)"

cd "$PROJECT_ROOT"

./vendor/bin/phinx migrate -e development
./vendor/bin/phinx seed:run -e development -s AddingFirstUser
./vendor/bin/phinx seed:run -e development -s AddingSectors
./vendor/bin/phinx seed:run -e development -s AddingPermissions
./vendor/bin/phinx seed:run -e development -s AddingPermissionsToAllSectorsToRootUser