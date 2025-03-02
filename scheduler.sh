#!/bin/bash

echo "Laravel Scheduler started at $(date)"

# Run the scheduler every minute
while true; do
    php /var/www/artisan schedule:run --verbose --no-interaction
    sleep 60
done
