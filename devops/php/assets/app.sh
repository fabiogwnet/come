#!/bin/bash
set -e

echo "Reload sysctl parameters"
sysctl -p;

echo "Updating config files..."

echo "Update hosts"

if grep -q "www.comexio.com.br" /etc/hosts; then
    echo "Exists"
else
    echo "$IP_HOST_API www.comexio.com.br" >> /etc/hosts;
fi

chmod 0644 /etc/cron.d/crontab
crontab /etc/cron.d/crontab

echo "Starting supervisor..."
/usr/bin/supervisord
