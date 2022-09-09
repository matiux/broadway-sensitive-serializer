#! /bin/bash

phpunit() {
  ./vendor/bin/phpunit \
    --configuration "$TOOLS_PATH"/phpunit/phpunit.xml.dist \
    --exclude-group learning \
    --colors=always \
    --testdox \
    --verbose \
    "$@"
}