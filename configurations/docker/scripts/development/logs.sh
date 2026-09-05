#!/usr/bin/env bash

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../../" && pwd)"

cd "$PROJECT_ROOT"

ENVIRONMENT=development \
MACHINE=docker \
docker compose \
    --env-file ".env.development.docker" \
    -f "./configurations/docker/compose.yml" \
    -f "./configurations/docker/compose.development.yml" \
    logs -f
