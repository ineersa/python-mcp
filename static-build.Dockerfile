FROM dunglas/frankenphp:static-builder

# For composer-in-build (your previous fix)
RUN apk add --no-cache php84-iconv

# Use SPC from source (already in the builder image path your script uses)
WORKDIR /go/src/app/dist/static-php-cli
RUN git pull || true
RUN composer install --no-dev -a --no-interaction

WORKDIR /work
COPY . /work/app

# You can tweak PHP/exts/libs here as envs too if you prefer
ENV SPC_OPT_DOWNLOAD_ARGS="--ignore-cache-sources=php-src --retry 5 --prefer-pre-built"
ENV SPC_OPT_BUILD_ARGS="--no-strip --disable-opcache-jit"

RUN ./dist/static-php-cli/bin/spc doctor --auto-fix && \
    ./dist/static-php-cli/bin/spc craft --with-clean /work/app/craft.yml

RUN mkdir -p /work/dist && \
    /work/buildroot/bin/php -d phar.readonly=0 -r '
      $base = "/work/app";
      $out  = "/work/dist/app.phar";
      @unlink($out);
      $p = new Phar($out);
      $p->buildFromDirectory($base);
      $stub = "#!/usr/bin/env php\n".
              "<?php Phar::mapPhar(\"app.phar\"); ".
              "chdir(\"phar://app.phar\"); ".
              "require \"bin/python-mcp\"; ".
              "__HALT_COMPILER();";
      $p->setStub($stub);
      echo "Built $out\n";
    ' && \
    chmod +x /work/dist/app.phar

RUN /work/spc/bin/spc micro:combine /work/dist/app.phar -O /work/dist/python-mcp
