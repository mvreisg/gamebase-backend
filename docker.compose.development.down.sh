#!/bin/bash
ENVIRONMENT=development MACHINE=docker docker compose --env-file .env.development.docker down -v