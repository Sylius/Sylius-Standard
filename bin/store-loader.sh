#!/usr/bin/env bash
set -euo pipefail

# -----------------------------------------------------------------------------
#   Script: store-loader.sh
#   Orchestrates the BUILD and/or DEPLOY steps for a Sylius store.
# -----------------------------------------------------------------------------

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
RESET='\033[0m'

usage() {
  cat <<EOF
${BOLD}Usage:${RESET} $(basename "$0") [--build] [--deploy]

  --build    Execute store build steps (plugins, fixtures, themes)
  --deploy   Execute store deployment steps (drop/create DB, update schema, load fixtures)

If no flags are provided, both sections will be executed sequentially.

Example:
  $(basename "$0") --build --deploy
EOF
  exit 1
}

# -----------------------------------------
# 1) Initialize flags
# -----------------------------------------
BUILD=false
DEPLOY=false

# If no arguments, run both by default
if [ $# -eq 0 ]; then
  BUILD=true
  DEPLOY=true
else
  # Parse arguments
  while [ "$#" -gt 0 ]; do
    case "$1" in
      --build)
        BUILD=true
        shift
        ;;
      --deploy)
        DEPLOY=true
        shift
        ;;
      *)
        usage
        ;;
    esac
  done

  # Require at least one valid flag if arguments were provided
  if ! $BUILD && ! $DEPLOY; then
    echo -e "${RED}Error:${RESET} You must specify at least --build or --deploy."
    usage
  fi
fi

# -----------------------------------------
# 2) Determine project root (one level up from this script)
# -----------------------------------------
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
STORE_PRESET_FILE="$PROJECT_DIR/store-preset/store-preset.json"

# -----------------------------------------
# 3) Retrieve store name from store-preset.json
# -----------------------------------------
if [ -f "$STORE_PRESET_FILE" ]; then
  STORE_NAME=$(php -r "echo json_decode(file_get_contents('$STORE_PRESET_FILE'), true)['name'] ?? '';"
)
else
  STORE_NAME=""
fi

# -----------------------------------------
# 4) If BUILD, execute BUILD steps
# -----------------------------------------
if $BUILD; then
  echo -e "${BLUE}${BOLD}=== [Store Loader] BUILD ===${RESET}"
  if [ -z "$STORE_NAME" ]; then
    echo -e "${YELLOW}Warning:${RESET} Missing store-preset or 'name' key in $STORE_PRESET_FILE."
    echo -e "To run BUILD, you must provide a valid store-preset/store-preset.json with a 'name'."
    echo -e "${GREEN}BUILD completed (no-op).${RESET}"
  else
    echo -e "Creating store: ${BOLD}$STORE_NAME${RESET}"
    echo

    echo -e "${BLUE}[Store Loader] PLUGINS${RESET}"
    php bin/console sylius:dx:plugin:prepare
    php bin/console cache:clear --no-warmup
    php bin/console cache:warmup
    php bin/console sylius:dx:plugin:install

    echo
    echo -e "${BLUE}[Store Loader] FIXTURES${RESET}"
    php bin/console sylius:dx:fixture:prepare

    echo
    echo -e "${BLUE}[Store Loader] THEMES${RESET}"
    php bin/console cache:clear --no-warmup
    php bin/console cache:warmup
    php bin/console sylius:dx:theme:prepare

    echo
    echo -e "${GREEN}[Store Loader] BUILD completed successfully.${RESET}"
  fi
fi

# -----------------------------------------
# 5) If DEPLOY, execute DEPLOY steps
# -----------------------------------------
if $DEPLOY; then
  echo
  echo -e "${BLUE}${BOLD}=== [Store Loader] DEPLOY ===${RESET}"
  echo -e "${BLUE}[Store Loader] PLUGINS (updating DB schema)${RESET}"
  php bin/console doctrine:database:drop --if-exists -n --force
  php bin/console doctrine:database:create -n
  php bin/console doctrine:schema:update -n --force --complete

  echo -e "Rebuilding the cache to ensure all configurations are properly loaded..."
  php bin/console cache:clear --no-warmup
  php bin/console cache:warmup

  if [ -z "$STORE_NAME" ]; then
    echo -e "${YELLOW}Warning:${RESET} No store configured in store-preset. Loading default fixtures."
    php bin/console sylius:fixtures:load -n
  else
    echo
    echo -e "${BLUE}[Store Loader] FIXTURES${RESET}"
    php bin/console sylius:dx:fixture:load
  fi

  echo
  echo -e "${GREEN}[Store Loader] DEPLOY completed successfully.${RESET}"
fi

exit 0
