#!/bin/bash

LOG_FILE="/var/www/storage/logs/scheduler.log"
echo "Laravel Scheduler started at $(date)" | tee -a "$LOG_FILE"

# Run the scheduler every minute
while true; do
    echo "Running scheduled tasks: $(date)" | tee -a "$LOG_FILE"
    php /var/www/artisan schedule:run --verbose --no-interaction >> "$LOG_FILE" 2>&1
    echo "---------------------------------" | tee -a "$LOG_FILE"
    sleep 60
done
