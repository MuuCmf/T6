#!/bin/sh

set -e

echo "Starting MuuCmf T6 Worker..."

mkdir -p /var/log/supervisor

echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO('mysql:host=mysql;dbname=muucmf', 'muucmf', 'muucmf_password'); echo 'OK'; } catch (Exception \$e) { echo 'WAIT'; sleep 2; }" | grep -q OK; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done

echo "MySQL is ready!"

echo "Waiting for Redis to be ready..."
until php -r "try { \$redis = new Redis(); \$redis->connect('redis', 6379); echo 'OK'; } catch (Exception \$e) { echo 'WAIT'; sleep 2; }" | grep -q OK; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done

echo "Redis is ready!"

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
