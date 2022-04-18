FROM php:8-alpine

COPY run.php /src/

ENTRYPOINT ["/src/run.php"]