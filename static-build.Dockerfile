FROM dunglas/frankenphp:static-builder

# Copy your app
WORKDIR /go/src/app/dist/app
COPY . .

# Ensure system PHP used by Composer has ext-iconv
RUN apk add --no-cache php84-iconv

# Caddy needs Go >= 1.25
ENV GOTOOLCHAIN=go1.25+auto

# Build the static binary
WORKDIR /go/src/app/

RUN PHP_EXTENSION_LIBS="bzip2,freetype,libavif,libjpeg,liblz4,libwebp,libzip,nghttp2,brotli,icu" \
    EMBED=dist/app/ \
    ./build-static.sh
