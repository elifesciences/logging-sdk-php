#!/usr/bin/env bash
set -e

composer install
rm -f build/*.xml
proofreader src/ tests/
vendor/bin/phpunit --log-junit build/phpunit.xml
