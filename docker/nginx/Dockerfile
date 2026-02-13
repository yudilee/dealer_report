FROM nginx:alpine

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom config
COPY conf.d/app.conf /etc/nginx/conf.d/app.conf
