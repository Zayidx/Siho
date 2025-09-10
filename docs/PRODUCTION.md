Production Setup Guide

1) PHP Runtime
- Enable OPcache with recommended settings in php.ini:
  - opcache.enable=1
  - opcache.enable_cli=1
  - opcache.validate_timestamps=0
  - opcache.max_accelerated_files=20000
  - opcache.memory_consumption=256
- Run PHP-FPM behind Nginx/Apache.

2) Environment
- In .env:
  - APP_ENV=production
  - APP_DEBUG=false
  - CACHE_STORE=redis
  - SESSION_DRIVER=redis
  - QUEUE_CONNECTION=redis
- Provision Redis and ensure REDIS_HOST/PORT/PASSWORD are set.

3) Build and Optimize
- composer install --no-dev --optimize-autoloader
- npm ci && npm run build
- php artisan migrate --force
- composer run optimize

4) Nginx Snippet (fast static files + gzip)

server {
    listen 80;
    server_name example.com;
    root /var/www/siho/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    # Cache-busted assets
    location ^~ /build/ {
        access_log off;
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Other static assets
    location ~* \.(?:jpg|jpeg|gif|png|svg|webp|ico|css|js|woff2?|ttf)$ {
        access_log off;
        expires 30d;
        add_header Cache-Control "public";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_read_timeout 60s;
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript application/xml+rss application/xml application/x-font-ttf image/svg+xml;
}

5) Queue & Horizon (Optional)
- Use Redis queues and run workers via systemd or Supervisor.
- Consider Laravel Horizon for visibility and auto-scaling.

6) Monitoring
- Enable application logs, use slow query log in MySQL.
- Optional: integrate with Sentry/Health checks.

