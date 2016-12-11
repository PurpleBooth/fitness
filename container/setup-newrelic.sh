#!/usr/bin/env bash

set -euo pipefail

if [[ ! -z ${NEW_RELIC_APP_NAME:+set}  && ! -z ${NEW_RELIC_LICENSE_KEY:+set} ]]; then
    NR_INSTALL_SILENT=1 NR_INSTALL_KEY="${NEW_RELIC_LICENSE_KEY}" newrelic-install install

    cat > /usr/local/etc/php/conf.d/newrelic.ini <<- EOM
extension=newrelic.so
newrelic.appname="${NEW_RELIC_APP_NAME}"
newrelic.license="${NEW_RELIC_LICENSE_KEY}"
EOM

fi