FROM php:8.1-cli-alpine

# Install basic dependencies
RUN apk --no-cache update && \
    apk --no-cache upgrade && \
    apk --no-cache add \
        ca-certificates \
        curl \
        libssl1.1

# Install bombardier
COPY --from=alpine/bombardier:latest /gopath/bin/bombardier /bin/bombardier

# Install Python 3
RUN apk add --no-cache python3 py3-pip
# Install DDoS Ripper
COPY --from=nitupkcuf/ddos-ripper:latest /app/DRipper.py /opt/ddos-ripper/DRipper.py

# Install DNSPerf
# TODO: compile as a separate stage
ENV DNSPERF_VERSION 2.9.0
RUN apk add --no-cache \
    bind \
    bind-dev \
    g++ \
    json-c-dev \
    krb5-dev \
    libcap-dev \
    libxml2-dev \
    make \
    autoconf \
    automake \
    libtool \
    pkgconfig \
    openssl-dev \
    nghttp2-dev \
    ldns-dev
# ck and ck-dev are only available in testing repo in alpine edge
RUN apk add --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/testing \
    ck \
    ck-dev
RUN mkdir -p /tmp/build/                                                      &&\
    curl https://www.dns-oarc.net/files/dnsperf/dnsperf-${DNSPERF_VERSION}.tar.gz \
      -o /tmp/build/dnsperf-${DNSPERF_VERSION}.tar.gz &&\
    tar -zxf /tmp/build/dnsperf-${DNSPERF_VERSION}.tar.gz \
      -C /tmp/build/                                                &&\
    cd /tmp/build/dnsperf-${DNSPERF_VERSION}/                         &&\
    ./configure --prefix=/                                                &&\
    make install                                                          &&\
    cd /tmp                                                               &&\
    rm -rf /tmp/build

# Download sample query file
COPY ./queryfile-example-current.gz /opt/queryfile-example-current.gz
RUN cd /opt &&\
    gunzip queryfile-example-current.gz &&\
    mv queryfile-example-current queryfile

# Install and enable pcntl
# TODO: compile as a separate stage and copy xdebug.so file from it
RUN apk add --no-cache $PHPIZE_DEPS
RUN docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl

COPY . /app
COPY --from=nitupkcuf/ddos-ripper:latest /app/headers.txt /app/headers.txt
RUN rm /app/queryfile-example-current.gz

WORKDIR /app

ENTRYPOINT ["/app/entrypoint.sh"]
CMD [""]
