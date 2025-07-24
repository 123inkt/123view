ARG VERSION=9.4

FROM mysql:${VERSION}

COPY conf.d/sqlmode.cnf /etc/mysql/conf.d/sqlmode.cnf
RUN chmod 644 /etc/mysql/conf.d/sqlmode.cnf

COPY conf.d/bin-log.cnf /etc/mysql/conf.d/bin-log.cnf
RUN chmod 644 /etc/mysql/conf.d/bin-log.cnf

RUN mkdir -p /var/lib/mysql-files
RUN chmod -R 775 /var/lib/mysql-files
