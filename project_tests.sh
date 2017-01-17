#!/usr/bin/env bash
set -e

: "${dependencies:?Need to set dependencies environment variable}"
if [ "$dependencies" = "lowest" ]; then
    composer update --prefer-lowest --no-interaction
    proofreader src/ test/
else
    composer update --no-interaction
fi
rm -f build/*.xml
proofreader src/ tests/
vendor/bin/phpunit --log-junit build/phpunit.xml
