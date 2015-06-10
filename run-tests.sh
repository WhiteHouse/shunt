#!/usr/bin/env sh

# NAME
#     run-tests.sh - Runs automated tests for the Shunt module.
#
# SYNOPSIS
#     run-tests.sh [options]
#
# DESCRIPTION
#     run-tests.sh runs all the Shunt module's automated tests. It requires
#     Drush. See http://drush.org/ for installation instructions. Note: If
#     running Simpletest tests, you will be prompted for your sudo password to
#     run as the web server user.
#
# OPTIONS
#     -l uri
#         The URI to pass to Simpletest. Defaults to "http://d8.dev/".
#
#     -s
#         Run Simpletest tests only.
#
#     -u
#         Run Unish (Drush) tests only.
#
#     -w web_server_user
#         The shell user the web server runs under. Defaults to "www-data".

# Set option defaults.
RUN_UNISH=1
RUN_SIMPLETEST=1
WEB_SERVER_USER="www-data"
URI="http://d8.dev/"

# Parse options.
while getopts "l:suw:" OPT; do
  case ${OPT} in
    l) URI=$OPTARG;;
    s) RUN_UNISH=0;;
    u) RUN_SIMPLETEST=0;;
    w) WEB_SERVER_USER=$OPTARG;;
  esac
done

# If Simpletest is selected to run, the script will need sudo access later on.
# Force a password prompt up-front so the rest of the run can be hands-free.
if [ ${RUN_SIMPLETEST} = 1 ]; then
  sudo true
fi

# Get the directory the current script is in.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Abort if Drush isn't available.
if ! command -v drush >/dev/null 2>&1; then
  echo >&2 "This script requires Drush, which is not currently installed. Visit http://drush.org/ to learn how to install it."
  exit 1
fi

# Get the directory Drush is installed in.
DRUSH_DIR="$(dirname "$( drush status 'Drush script' --format=list )" )"

# Get the Drupal root directory. Abort if none is found.
DRUPAL_ROOT=$(drush dd 2>&1)
if [ $? -eq 1 ]; then
  echo >&2 "The Drupal root directory could not be found. Please run from within a functioning Drupal site."
  exit 1
fi

# Run Unish tests.
if [ ${RUN_UNISH} = 1 ]; then
  echo "Running Unish tests..."
  ${DRUPAL_ROOT}/core/vendor/bin/phpunit --configuration="${DRUSH_DIR}/tests" "${SCRIPT_DIR}/drush"
  echo
fi

# Run Simpletest tests.
if [ ${RUN_SIMPLETEST} = 1 ]; then
  echo "Running Simpletest tests..."
  sudo -u ${WEB_SERVER_USER} php ${DRUPAL_ROOT}/core/scripts/run-tests.sh \
    --url ${URI} \
    --concurrency 5 \
    --color \
    shunt,shuntexample
  echo
fi
