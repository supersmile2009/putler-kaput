Initialize and run the workload with 100 containers. Replace 100 with other number to fit your needs. 
```shell
sudo apt-get install curl
curl --request GET -sL \
     --url 'https://raw.githubusercontent.com/supersmile2009/putler-kaput/main/init.sh'\
     --output '/tmp/putler-kaput-init.sh'
chmod +x /tmp/putler-kaput-init.sh
sudo /tmp/putler-kaput-init.sh 100
```

Once running you can adjust the workload (container count) by editing `/etc/cron.d/putler_kaput`
