FROM --platform=linux/amd64 dunglas/frankenphp:static-builder

# Copy your app
WORKDIR /go/src/app/dist/app
COPY . .

# Ensure system PHP used by Composer has ext-iconv
RUN apk add --no-cache php84-iconv

# Build the static binary
WORKDIR /go/src/app/

RUN EMBED=dist/app/ ./build-static.sh
