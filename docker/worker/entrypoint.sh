#!/bin/sh

set -e

echo "Starting MuuCmf T6 Worker..."

mkdir -p /var/log/supervisor

echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO('mysql:host=' . getenv('database.hostname') . ';dbname=' . getenv('database.database'), getenv('database.username'), getenv('database.password')); echo 'OK'; } catch (Exception \$e) { echo 'WAIT'; }" | grep -q OK; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is ready!"

echo "Waiting for Redis to be ready..."
until php -r "try { \$redis = new Redis(); \$redis->connect(getenv('redis.host'), getenv('redis.port')); if (getenv('redis.password')) { \$redis->auth(getenv('redis.password')); } echo 'OK'; } catch (Exception \$e) { echo 'WAIT'; }" | grep -q OK; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done

echo "Redis is ready!"

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
