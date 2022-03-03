#!/bin/sh

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- /usr/local/bin/php /app/bin/console app:client:run "$@"
fi

exec "$@"
