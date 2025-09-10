#!/usr/bin/env bash
set -euo pipefail
PROJECT_DIR="$PWD"

# Export the project to get rid of .git/, etc
rm -Rf /tmp/python-mcp
mkdir /tmp/python-mcp
git archive HEAD | tar -x -C /tmp/python-mcp
cd /tmp/python-mcp

# Set proper environment variables
echo APP_ENV=prod > .env.local
echo APP_DEBUG=0 >> .env.local

# Remove the tests and other unneeded files to save space
# Alternatively, add these files with the export-ignore attribute in your .gitattributes file
rm -Rf ./tests/
rm -Rf ./var/
rm -Rf ./dist/
rm ./bin/phpunit

# Install the dependencies
/usr/bin/php8.4 /usr/local/bin/composer install --no-interaction --quiet --ignore-platform-reqs --no-dev -a

# Optimize .env
/usr/bin/php8.4 /usr/local/bin/composer dump-env --no-interaction --quiet prod

docker build -t static-app -f static-build.Dockerfile .

mkdir -p ./dist
docker create --name static-app-tmp static-app
docker cp static-app-tmp:/go/src/app/dist/. dist/
docker rm static-app-tmp

cp -r ./dist "${PROJECT_DIR}"



