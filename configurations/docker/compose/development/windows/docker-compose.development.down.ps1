cd ../../../../../

$env:ENVIRONMENT="development"
$env:MACHINE="docker"

docker-compose --env-file .env.development.docker down -v