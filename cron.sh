#!/bin/bash

max_containers=${1}
set -e

curl --request GET -sL \
     --url 'https://raw.githubusercontent.com/supersmile2009/putler-kaput/main/worker.sh'\
     --output '/opt/putler-kaput/worker.sh'

chmod +x /opt/putler-kaput/worker.sh
/opt/putler-kaput/worker.sh "${max_containers}"
