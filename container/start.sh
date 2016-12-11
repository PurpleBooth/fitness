#!/usr/bin/env bash

set -euo pipefail

ENV="${SYMFONY_ENV:-dev}"
export SYMFONY_ENV="$ENV"


cd "$CODE_DIR"

$CODE_DIR/container/setup-newrelic.sh
$CODE_DIR/container/setup-blackfire.sh
$CODE_DIR/container/composer-install.sh

if [ $# -lt 1 ] ; then
    $CODE_DIR/bin/console cache:warmup --env="${SYMFONY_ENV}"
fi


chmod -R a+rxw "$CODE_DIR/var/cache" \
               "$CODE_DIR/var/sessions" \
               "$CODE_DIR/var/logs"


if [ $# -gt 0 ] ; then
    exec bash -c "$@"
else
#    $CODE_DIR/bin/console doctrine:migrations:migrate --env="${SYMFONY_ENV}" -n
    exec apache2-foreground
fi