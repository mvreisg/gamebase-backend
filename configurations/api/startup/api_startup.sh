#!/bin/bash
cd "$(dirname "$0")/../../../"
./vendor/bin/openapi src/Presentation/Http/Controller src/Presentation/Http/OpenApi -o public/docs/openapi.json