FROM node:16-bullseye

WORKDIR /app

COPY ./docker/nodejs/run/entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh
ENTRYPOINT ["/run/entrypoint.sh"]
