FROM dunglas/frankenphp:static-builder

RUN apk add --no-cache php84-iconv

WORKDIR /go/src/app/dist/static-php-cli
RUN git pull || true
RUN composer install --no-dev -a --no-interaction

WORKDIR /work
COPY . /work/app

ENV SPC_OPT_DOWNLOAD_ARGS="--ignore-cache-sources=php-src --retry 5 --prefer-pre-built"
ENV SPC_OPT_BUILD_ARGS="--no-strip --disable-opcache-jit"

RUN /go/src/app/dist/static-php-cli/bin/spc doctor --auto-fix && \
    /go/src/app/dist/static-php-cli/bin/spc craft --with-clean /work/app/craft.yml

RUN mkdir -p /work/dist && \
    cat > /tmp/build_phar.php <<'PHP' && \
    /work/buildroot/bin/php -d phar.readonly=0 /tmp/build_phar.php && \
    rm /tmp/build_phar.php && \
    chmod +x /work/dist/app.phar
<?php
$base = "/work/app";
$out  = "/work/dist/app.phar";
@unlink($out);
$p = new Phar($out);
$p->buildFromDirectory($base);
$stub = "#!/usr/bin/env php\n"
      . "<?php Phar::mapPhar(\"app.phar\"); "
      . "putenv('APP_ENV=prod'); putenv('APP_DEBUG=0'); "
      . "chdir(\"phar://app.phar\"); "
      . "require \"bin/python-mcp\"; "
      . "__HALT_COMPILER();";
$p->setStub($stub);
echo "Built $out\n";
PHP

RUN /go/src/app/dist/static-php-cli/bin/spc micro:combine /work/dist/app.phar -O /work/dist/python-mcp
