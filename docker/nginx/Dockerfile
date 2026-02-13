FROM nginx:alpine

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom config (path relative to project root)
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d/app.conf

# Copy public directory for static file serving
COPY public /var/www/public
