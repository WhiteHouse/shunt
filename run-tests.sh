#!/usr/bin/env sh

# @file
# Runs automated tests for the Shunt module.
#
# Usage ./run-tests.sh [$WEBSERVER_USER] (defaults to "www-data") [$URI]
#   (defaults to "http://d8.dev/")
#   e.g., ./run-tests.sh
#   or ./run-tests.sh apache http://example.com/
#
# Note: You will be prompted for your sudo password to run as the webserver
# user.

# Get the directory the current script is in.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Abort if Drush isn't available.
if ! command -v drush >/dev/null 2>&1; then
  echo >&2 "This script requires Drush, which is not currently installed. Visit http://drush.org/ to learn how to install it."
  exit 1
fi

# Get the Drupal root directory. Abort if none is found.
DRUPAL_ROOT=$(drush dd 2>&1)
if [ $? -eq 1 ]; then
  echo >&2 "The Drupal root directory could not be found. Please run from within a functioning Drupal site."
  exit 1
fi

# Run PHPUnit tests.
echo "Running PHPUnit tests..."
${DRUPAL_ROOT}/core/vendor/bin/phpunit --configuration="$DRUPAL_ROOT/core" "$SCRIPT_DIR/tests/src"
echo

# Run Unish tests.
echo "Running Unish tests..."
UNAME=`uname`
if [ ${UNAME} = "Linux" ]; then
  DRUSH_PATH="`readlink -f $(which drush)`"
elif [ ${UNAME} = "Darwin" ] || [ ${UNAME} = "FreeBSD" ]; then
  DRUSH_PATH="`realpath $(which drush)`"
fi
DRUSH_DIR="`dirname -- "$DRUSH_PATH"`"
${DRUPAL_ROOT}/core/vendor/bin/phpunit --configuration="$DRUSH_DIR/tests" "$SCRIPT_DIR/drush"
echo

# Run Simpletest tests.
echo "Running Simpletest tests..."
WEBSERVER_USER=${1:-"www-data"}
URI=${2:-"http://d8.dev/"}
sudo -u ${WEBSERVER_USER} php ${DRUPAL_ROOT}/core/scripts/run-tests.sh --url ${URI} shunt
echo
