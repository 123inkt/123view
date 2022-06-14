ARG VERSION=8.0

FROM mysql:${VERSION} as prod

ARG PIPELINE_NUMBER=dev
ENV BUILDNUMBER=$PIPELINE_NUMBER

COPY conf.d/sqlmode.cnf /etc/mysql/conf.d/sqlmode.cnf
RUN chmod 644 /etc/mysql/conf.d/sqlmode.cnf

RUN mkdir -p /var/lib/mysql-files
RUN chmod -R 775 /var/lib/mysql-files

FROM prod as dev
