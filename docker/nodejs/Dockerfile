FROM node:21

WORKDIR /app

RUN apt-get update && apt-get install -y supervisor

COPY ./docker/nodejs/run/entrypoint.sh /run/entrypoint.sh
COPY ./docker/nodejs/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
