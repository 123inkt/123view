ARG VERSION=8.3

FROM mysql:${VERSION}

COPY conf.d/sqlmode.cnf /etc/mysql/conf.d/sqlmode.cnf
COPY conf.d/bin-log.cnf /etc/mysql/conf.d/bin-log.cnf
COPY conf.d/memory.cnf /etc/mysql/conf.d/memory.cnf

RUN chmod 644 /etc/mysql/conf.d/*.cnf
RUN mkdir -p /var/lib/mysql-files
RUN chmod -R 775 /var/lib/mysql-files
