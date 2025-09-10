FROM dunglas/frankenphp:static-builder

# For composer-in-build (your previous fix)
RUN apk add --no-cache php84-iconv

WORKDIR /go/src/app
COPY . .

# Use SPC from source (already in the builder image path your script uses)
WORKDIR /go/src/app/dist/static-php-cli
RUN git pull || true
RUN composer install --no-dev -a --no-interaction

# Build single-file CLI
WORKDIR /go/src/app
COPY craft.yml .

# You can tweak PHP/exts/libs here as envs too if you prefer
ENV SPC_OPT_DOWNLOAD_ARGS="--ignore-cache-sources=php-src --retry 5 --prefer-pre-built"
ENV SPC_OPT_BUILD_ARGS="--no-strip --disable-opcache-jit"

RUN ./dist/static-php-cli/bin/spc doctor --auto-fix && \
    ./dist/static-php-cli/bin/spc craft build --with-clean /go/src/app/craft.yml
