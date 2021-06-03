ARG NGINX_VERSION=1.17

FROM nginx:${NGINX_VERSION}-alpine AS sylius_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

WORKDIR /srv/sylius

COPY --from=sylius/server:latest /srv/sylius/public public/
COPY --from=sylius/assets:latest /srv/sylius/public public/
