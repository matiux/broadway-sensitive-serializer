#!/usr/bin/env bash

WORKDIR=/var/www/app
PROJECT_NAME=$(basename "$(pwd)" | tr '[:upper:]' '[:lower:]')
COMPOSE_OVERRIDE=
PHP_CONTAINER=php_broadway_sensitised_es_dbal

if [[ -f "./docker/docker-compose.override.yml" ]]; then
  COMPOSE_OVERRIDE="--file ./docker/docker-compose.override.yml"
fi

DC_BASE_COMMAND="docker-compose
    --file docker/docker-compose.yml
    -p ${PROJECT_NAME}
    ${COMPOSE_OVERRIDE}"

  DC_EXEC="${DC_BASE_COMMAND}
    exec
    -u utente
    -T
    -w ${WORKDIR}
    ${PHP_CONTAINER}"

if [[ "$1" == "composer" ]]; then

  shift 1
  ${DC_EXEC} \
    composer "$@"

elif [[ "$1" == "php-cs-fixer-fix" ]]; then

  shift 1
  ${DC_EXEC} \
    vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php "$@"

elif [[ "$1" == "php-cs-fixer" ]]; then

  shift 1
  ${DC_EXEC} \
    vendor/bin/php-cs-fixer "$@"

elif [[ "$1" == "psalm" ]]; then

  shift 1
  ${DC_EXEC} \
    vendor/bin/psalm "$@"

elif [[ "$1" == "phpunit" ]]; then

  shift 1
  ${DC_EXEC} \
    vendor/bin/simple-phpunit "$@"

elif [[ "$1" == "coverage-badge" ]]; then

  shift 1
  ${DC_EXEC} \
    scripts/commands/update-coverage-badge

elif [[ "$1" == "generate-coverage" ]]; then

  shift 1
  ${DC_EXEC} \
    scripts/commands/generate-coverage

elif [[ "$1" == "badge" ]]; then

  shift 1

  rm -f "public/$3.svg"
  rm -f "public/$3.png"

  ${DC_EXEC} \
    vendor/bin/poser \
      "$1" \
      "$2" \
      green \
      -p "public/$3.svg" \
      -s plastic \
        &>/dev/null

  ${DC_EXEC} \
    inkscape --export-png="public/$3.png" \
      --export-dpi=200 \
      --export-background-opacity=0 \
      --without-gui "public/$3.svg" \
        &>/dev/null

elif [[ "$1" == "deptrac" ]]; then

  shift 1
  #imgFileName=$(echo $1 | tr "/" "\n" | tail -1 | sed 's/\.yaml/\.png/')

  ${DC_EXEC} \
    php deptrac.phar analyse "$1"

elif [[ "$1" == "up" ]]; then

  shift 1
  ${DC_BASE_COMMAND} \
    up "$@"

elif [[ "$1" == "build" ]] && [[ "$2" == "php" ]]; then

  ${DC_BASE_COMMAND} \
    build --force ${PHP_CONTAINER}

elif [[ "$1" == "enter-root" ]]; then

  ${DC_BASE_COMMAND} \
    exec \
    -u root \
    ${PHP_CONTAINER} /bin/zsh

elif [[ "$1" == "enter" ]]; then

  ${DC_BASE_COMMAND} \
    exec \
    -u utente \
    -w ${WORKDIR} \
    ${PHP_CONTAINER} /bin/zsh

elif [[ "$1" == "down" ]]; then

  shift 1
  ${DC_BASE_COMMAND} \
    down "$@"

elif [[ "$1" == "purge" ]]; then

  ${DC_BASE_COMMAND} \
    down \
    --rmi=all \
    --volumes \
    --remove-orphans

elif [[ "$1" == "log" ]]; then

  ${DC_BASE_COMMAND} \
    logs -f

elif [[ $# -gt 0 ]]; then

  ${DC_BASE_COMMAND} \
    "$@"

else

  ${DC_BASE_COMMAND} \
    ps
fi
