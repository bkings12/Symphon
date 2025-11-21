#!/bin/bash
# Script to monitor payment logs in real-time
echo "Monitoring Laravel logs for payment entries..."
echo "Run a test transaction now..."
echo ""
tail -f /home/bryan/Symphony/storage/logs/laravel.log | grep --line-buffered -A 5 "Cash payment\|Payment created\|Payment for receipt"

