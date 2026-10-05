#!/bin/bash

# Script to run PHPUnit tests
# Usage: ./tests/run-phpunit.sh [options]
# Example: ./tests/run-phpunit.sh --filter SanitizeTest

echo "=== Running PHPUnit Tests ==="
echo ""

# Run PHPUnit with LD_PRELOAD to fix GLIBCXX version issues
LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libstdc++.so.6 ./vendor/bin/phpunit "$@"

exit $?
