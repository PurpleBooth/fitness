#!/usr/bin/env bash

set -euo pipefail

SYMFONY_ENV="${SYMFONY_ENV:-dev}"

if [ "${DATABASE_DEFAULT_DRIVER:-pdo_sqlite}" == "pdo_mysql" ] ; then
    export DATABASE_DEFAULT_PATH="${DATABASE_DEFAULT_NAME:-backoffice}"
fi

if [ "${DATABASE_OAUTH_DRIVER:-pdo_sqlite}" == "pdo_mysql" ] ; then
    export DATABASE_OAUTH_PATH="${DATABASE_OAUTH_NAME:-kiikplan}"
fi

cd "${CODE_DIR}"

if [ ! -f "composer.phar" ]
then
    php -r "copy('https://getcomposer.org/composer.phar', 'composer.phar');"
fi

if [ ! -f ~/.ssh/known_hosts ];
then
    mkdir -p /root/.ssh/;
    touch ~/.ssh/known_hosts
fi

if ! grep "github.com" ~/.ssh/known_hosts > /dev/null ; then
    ssh-keyscan -H github.com >> ~/.ssh/known_hosts
fi

if [ "${COMPOSER_GITHUB_TOKEN:-}" != "" ]
then
    php composer.phar config -n -g github-oauth.github.com "$COMPOSER_GITHUB_TOKEN"
fi


if [ "${SYMFONY_ENV:-dev}" == "dev" ] ; then
    php "composer.phar" install -n
else
    php "composer.phar" install -n -o --no-dev
fi