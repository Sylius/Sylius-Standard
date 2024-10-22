FROM nginx:alpine

COPY ./files/nginx.conf /etc/nginx/nginx.conf
COPY ./files/sites/default.conf /etc/nginx/sites-available/default.conf

CMD ["nginx"]

WORKDIR /var/www