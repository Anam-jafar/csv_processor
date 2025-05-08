FROM nginx:alpine

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

RUN mkdir -p /var/log/nginx && \
    chown -R nginx:nginx /var/log/nginx

EXPOSE 80 