#!/bin/sh

getRamMb() {
    ram_kb=$(grep MemTotal /proc/meminfo | awk '{print $2}')
    ram_mb=$(( ram_kb / 1024 ))
    echo "${ram_mb}"
}

getMaxConcurrentProc() {
  ram_mb=$(getRamMb)
  proc_count=$(( (ram_mb - 400) / 200 ))
  proc_count=$(( proc_count > 0 ? proc_count : 1 ))
  echo "${proc_count}"
}

# first arg is `-f` or `--some-option` or no argument provided
if [ "${1#-}" != "$1" ] || [ "${1}" = "" ]; then
  c_opt_found="false"
  for opt in "$@"; do
    if [ "${opt}" != "${opt#-c}" ]; then
      c_opt_found="true"
    fi
  done
  if [ "${c_opt_found}" = "false" ]; then
    max_proc=$(getMaxConcurrentProc)
    if [ "${1}" = "" ]; then
      set -- "-c${max_proc}"
    else
      set -- "-c${max_proc}" "$@"
    fi
  fi

	set -- /usr/local/bin/php /app/bin/console app:client:run "$@"
fi

exec "$@"
