FROM php:8-alpine

COPY *.php /src/

ENTRYPOINT ["/src/run.php"]