FROM matiux/php:7.4-fpm-bullseye-dev

USER root

###> Dipendenze sviluppo
RUN apt-get update \
    && apt-get -y upgrade
###> Fine dipendenze sviluppo

COPY conf/xdebug-starter.sh /usr/local/bin/xdebug-starter
RUN chmod +x /usr/local/bin/xdebug-starter
RUN /usr/local/bin/xdebug-starter

USER utente

###> Bashrc con alias
COPY conf/shell-alias.rc /tmp/shell-alias.rc
RUN cat /tmp/shell-alias.rc >> /home/utente/.zshrc \
    && cat /tmp/shell-alias.rc >> /home/utente/.bashrc
###> Fine bashrc con alias

USER www-data