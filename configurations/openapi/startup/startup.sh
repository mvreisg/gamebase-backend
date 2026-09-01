#!/usr/bin/env bash

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../" && pwd)"
cd "$PROJECT_ROOT"

./vendor/bin/openapi \
    src/Presentation/Http/Controller \
    src/Presentation/Http/OpenApi \
    -o public/docs/openapi.json