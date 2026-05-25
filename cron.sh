#!/bin/bash

# Crunz dispatcher — drop this in crontab once per server:
#
#   * * * * * /path/to/odkdash/cron.sh >> /path/to/odkdash/var/log/crunz.log 2>&1
#
# The schedule lives in tasks/GeneralTasks.php and runs from there. This
# script just resolves a script-relative path to vendor/bin/crunz so the
# crontab line stays short and doesn't break if the repo moves.

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

"$SCRIPT_DIR/vendor/bin/crunz" schedule:run
