#!/bin/bash

set -e

DEPLOY_ENV=${1:-production}
DEPLOY_DIR="/var/www/${DEPLOY_ENV}.muucmf.cc"
CURRENT_DIR="${DEPLOY_DIR}/current"

echo "========================================="
echo "MuuCmf T6 Health Check Script"
echo "Environment: ${DEPLOY_ENV}"
echo "========================================="

HEALTH_URL="https://${DEPLOY_ENV}.muucmf.cc/health"
if [ "$DEPLOY_ENV" == "production" ]; then
    HEALTH_URL="https://www.muucmf.cc/health"
elif [ "$DEPLOY_ENV" == "staging" ]; then
    HEALTH_URL="https://staging.muucmf.cc/health"
fi

echo "Checking health endpoint: $HEALTH_URL"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" || echo "000")

if [ "$HTTP_CODE" == "200" ]; then
    echo "✓ Health check passed (HTTP $HTTP_CODE)"
    
    echo "Checking database connection..."
    cd "$CURRENT_DIR"
    if php think db:check 2>/dev/null; then
        echo "✓ Database connection OK"
    else
        echo "✗ Database connection failed"
        exit 1
    fi
    
    echo "Checking Redis connection..."
    if php think redis:check 2>/dev/null; then
        echo "✓ Redis connection OK"
    else
        echo "✗ Redis connection failed"
        exit 1
    fi
    
    echo "Checking queue worker..."
    if pgrep -f "queue:work" > /dev/null; then
        echo "✓ Queue worker running"
    else
        echo "✗ Queue worker not running"
        exit 1
    fi
    
    echo "========================================="
    echo "All health checks passed!"
    echo "========================================="
    exit 0
else
    echo "✗ Health check failed (HTTP $HTTP_CODE)"
    echo "========================================="
    echo "Health checks failed!"
    echo "========================================="
    exit 1
fi
