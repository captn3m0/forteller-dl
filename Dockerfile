FROM php:8-slim

COPY *.php /

ENTRYPOINT ["/usr/bin/php", "/src/run.php"]