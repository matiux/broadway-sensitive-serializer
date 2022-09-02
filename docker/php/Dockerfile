FROM matiux/php:fpm-7.4-bullseye-dev

USER root

RUN apt-get update && apt-get install -y --no-install-recommends \
    jq

###> Dipendenze sviluppo
RUN apt-get update \
    && apt-get -y upgrade \
    && apt-get install -y --no-install-recommends \
    graphviz #Per deptrac

RUN /usr/local/bin/composer self-update
###> Fine dipendenze sviluppo

###> Read The Docs
RUN apt-get install -y --no-install-recommends \
    python3-pip \
    python3-sphinx

RUN pip install \
    sphinx-tabs \
    sphinx_rtd_theme \
    sphinxcontrib-phpdomain \
    sphinx-autobuild \
    sphinx-hoverxref
###> Fine Read The Docs

COPY ./docker/php/conf/xdebug-starter.sh /usr/local/bin/xdebug-starter
RUN chmod +x /usr/local/bin/xdebug-starter
RUN /usr/local/bin/xdebug-starter

USER utente

###> Bashrc con alias
COPY ./docker/php/conf/shell-custom.rc /tmp/shell-custom.rc
RUN cat /tmp/shell-custom.rc >> /home/utente/.zshrc \
    && cat /tmp/shell-custom.rc >> /home/utente/.bashrc
###> Fine bashrc con alias

USER www-data