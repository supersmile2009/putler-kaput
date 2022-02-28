#!/bin/bash

container_count="${1}"

# Using Docker CE directly provided by Docker
echo "[INFO] [${SCRIPT}] Setup docker"
sudo apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
#sudo DEBIAN_FRONTEND=noninteractive apt-get -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" upgrade
#sudo DEBIAN_FRONTEND=noninteractive apt-get -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" dist-upgrade
apt-cache policy docker-ce docker-ce-cli containerd.io

sudo apt-get install -y docker-ce docker-ce-cli containerd.io


# init Swarm to conveniently launch multiple replicas of the app
sudo docker swarm init --listen-addr 127.0.0.1


# install bash-commons
/usr/bin/cloud-init status --wait
sudo mkdir -p /opt/gruntwork
git clone --branch v0.1.9 https://github.com/gruntwork-io/bash-commons.git /tmp/bash-commons
sudo cp -r /tmp/bash-commons/modules/bash-commons/src /opt/gruntwork/bash-commons

# allow more open files
sysctl -w fs.file-max=500000
grep -q "fs.file-max" /etc/sysctl.conf && sed -r -i "s/#?\s?fs\.file-max.*/fs.file-max = 500000/g" /etc/sysctl.conf || echo "fs.file-max = 500000" >> /etc/sysctl.conf

mkdir -p /opt/putler-kaput
curl --request GET -sL \
     --url 'https://raw.githubusercontent.com/supersmile2009/putler-kaput/main/cron.sh'\
     --output '/opt/putler-kaput/cron.sh'
chmod +x /opt/putler-kaput/cron.sh

# schedule a cron job
echo "*/3 * * * * root /bin/bash /opt/putler-kaput/cron.sh ${container_count}" > /etc/cron.d/putler_kaput
sudo service cron reload
