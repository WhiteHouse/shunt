#!/usr/bin/env sh

# @file
# Runs automated tests for Shunt module.
#
# This script assumes that the module is installed in
# [drupal-docroot]/modules/contrib/shunt.
#
# Usage ./run-tests [webserver-user] (defaults to "www-data") [URI] (defaults to
#   "http://d8.dev/"
#   e.g., ./run-tests
#   or ./run-tests apache http://example.com/
#
# Note: You will be prompted for your sudo password to run as the webserver
# user.

WEBSERVER_USER=${1:-"www-data"}
URI=${2:-"http://d8.dev/"}

run_phpunit_tests() {
  rm -rf tests/coverage
  cd ../../../core/
  ./vendor/bin/phpunit \
    --coverage-html ../modules/contrib/shunt/tests/coverage \
    ../modules/contrib/shunt/tests/src/
}

run_simpletest_tests() {
  sudo -u ${WEBSERVER_USER} php scripts/run-tests.sh --url ${URI} Shunt
}

run_phpunit_tests
run_simpletest_tests
