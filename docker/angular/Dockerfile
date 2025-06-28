FROM node:22

WORKDIR /app

COPY ./docker/angular/run/entrypoint.sh /run/entrypoint.sh

RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
