FROM php:8.1-cli-alpine

# Install and enable pcntl
RUN docker-php-ext-install pcntl

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
ARG DRIPPER_VERSION=1.3.9
RUN mkdir -p /tmp/dripper &&\
    curl -sL https://github.com/alexmon1989/russia_ddos/archive/refs/tags/${DRIPPER_VERSION}.tar.gz \
      -o /tmp/dripper/dripper.tar.gz &&\
    tar -zxf /tmp/dripper/dripper.tar.gz \
      -C /tmp/dripper/ &&\
    mkdir -p /opt/dripper &&\
    mv \
      /tmp/dripper/russia_ddos-${DRIPPER_VERSION}/DRipper.py \
      /tmp/dripper/russia_ddos-${DRIPPER_VERSION}/requirements.txt \
      /tmp/dripper/russia_ddos-${DRIPPER_VERSION}/ripper \
      /opt/dripper/ &&\
    pip install --upgrade pip &&\
    pip install -r /opt/dripper/requirements.txt &&\
    rm -rf /opt/dripper/requirements.txt &&\
    rm -rf /tmp/dripper

# Install DNSPerf
# ck and ck-dev are only available in testing repo in alpine edge
ARG DNSPERF_VERSION=2.9.0
RUN apk add --no-cache \
        openssl \
        nghttp2 \
        ldns &&\
    apk add --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/testing \
        ck
RUN apk add --no-cache --virtual .dnsperf-build \
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
        ldns-dev &&\
    apk add --no-cache --virtual .dnsperf-build2 --repository=https://dl-cdn.alpinelinux.org/alpine/edge/testing \
        ck-dev &&\
    mkdir -p /tmp/build/                                                      &&\
    curl https://www.dns-oarc.net/files/dnsperf/dnsperf-${DNSPERF_VERSION}.tar.gz \
      -o /tmp/build/dnsperf-${DNSPERF_VERSION}.tar.gz &&\
    tar -zxf /tmp/build/dnsperf-${DNSPERF_VERSION}.tar.gz \
      -C /tmp/build/                                                &&\
    cd /tmp/build/dnsperf-${DNSPERF_VERSION}/                         &&\
    ./configure --prefix=/                                                &&\
    make install                                                          &&\
    make clean distclean &&\
    cd /tmp                                                               &&\
    rm -rf /tmp/build &&\
    apk del --no-network .dnsperf-build .dnsperf-build2

# Download sample query file
COPY ./queryfile-example-current.gz /opt/queryfile-example-current.gz
RUN cd /opt &&\
    gunzip queryfile-example-current.gz &&\
    mv queryfile-example-current queryfile

COPY ./app /app
COPY entrypoint.sh /app/

WORKDIR /app

ENTRYPOINT ["/app/entrypoint.sh"]
CMD [""]
