# ============================================================
# 5. Nginx
# ============================================================
FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=app /var/www/html/public /var/www/html/public

RUN rm -rf /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]