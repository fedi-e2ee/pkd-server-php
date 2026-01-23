# Operators Manual

This section of the documentation contains information essential to operating an instance of the
Public Key Directory reference software (server-side).

> [!WARNING]
> This software is not in a stable enough state to deploy anywhere yet.
> 
> Please wait until the v1.0.0 major release has been tagged.

## Requirements

This software is written in **PHP** and requires **PHP 8.4 or newer**. It should be safe to run on any Operating System.

The Sodium cryptography library and associated PHP extension (`ext-sodium`) are highly recommended. A polyfill library
([sodium_compat](https://github.com/paragonie/sodium_compat)) is provided, but we cannot guarantee the best performance
when the Sodium extension is not available.

Three database backends are currently supported: MySQL / MariaDB, PostgreSQL, and SQLite. For best performance, we
recommend installing **Redis** in your deployment and configuring our software to use it.

Our software is largely webserver agnostic. Apache, nginx, or Caddy are all okay. Use whichever you're comfortable with.
We recommend **nginx** since it's widely used and [now has native ACME support](https://blog.nginx.org/blog/native-support-for-acme-protocol).
Deploying over HTTPS is **required**. Use LetsEncrypt with ACME. It works great.

## Example Webserver Configuration

> [!TIP]
> If you already know what "LAMP Stack" means, you can [skip this section](#configuring-the-public-key-directory).

These configurations assume you cloned the pkd-server-php repository to `/var/www/pkd-server-php`.
The file paths and directories provided commands provided assume you are running on Debian or Ubuntu Linux.

### Apache

**File:** `/etc/apache2/sites-available/demo.publickey.directory.conf`

```apacheconf
<VirtualHost *:80>
    ServerName demo.publickey.directory

    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName demo.publickey.directory
    DocumentRoot /var/www/pkd-server-php/public

    # TLS Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/demo.publickey.directory/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/demo.publickey.directory/privkey.pem

    # Modern TLS settings
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    SSLSessionTickets off

    # Security Headers (applied to all responses)
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; style-src 'self'"
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer"
    Header always set Permissions-Policy "accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), interest-cohort=()"

    # Remove server version disclosure
    Header always unset X-Powered-By
    ServerTokens Prod
    ServerSignature Off

    <Directory /var/www/pkd-server-php/public>
        Options -Indexes -FollowSymLinks
        AllowOverride None
        Require all granted

        # Front controller rewrite
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ index.php [L]
    </Directory>

    # Block access to sensitive files
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    # PHP-FPM via proxy
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.5-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/demo.publickey.directory-error.log
    CustomLog ${APACHE_LOG_DIR}/demo.publickey.directory-access.log combined
</VirtualHost>
```

Ensble these modules 

```bash
sudo a2enmod ssl rewrite headers proxy_fcgi setenvif
sudo a2ensite demo.publickey.directory
sudo systemctl reload apache2
```

> [!NOTE]
> It's fine to run this with Apache mod-php, but we recommend using FPM for best results.

### nginx

**File:** `/etc/nginx/sites-available/demo.publickey.directory`

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name demo.publickey.directory;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name demo.publickey.directory;

    root /var/www/pkd-server-php/public;
    index index.php;

    # TLS Configuration
    ssl_certificate /etc/letsencrypt/live/demo.publickey.directory/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/demo.publickey.directory/privkey.pem;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;

    # Modern TLS settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    # ^- better mobile device battery life

    # OCSP stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/demo.publickey.directory/chain.pem;
    resolver 1.1.1.1 1.0.0.1 valid=300s;
    resolver_timeout 5s;

    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header Content-Security-Policy "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; style-src 'self'" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer" always;
    add_header Permissions-Policy "accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), interest-cohort=()" always;
    # No permissions because we don't need them

    # Remove server version disclosure
    server_tokens off;

    # Front controller
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # Security: only execute index.php
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        try_files $fastcgi_script_name =404;

        # Hide PHP version
        fastcgi_hide_header X-Powered-By;
    }

    # Block access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Block access to non-public directories
    location ~ ^/(vendor|config|sql|tests|fuzzing|src)/ {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/demo.publickey.directory-access.log;
    error_log /var/log/nginx/demo.publickey.directory-error.log;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/demo.publickey.directory /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Caddy

**File:** `/etc/caddy/Caddyfile`

```caddy

demo.publickey.directory {
    root * /var/www/pkd-server-php/public

    # TLS is automatic with Caddy (ACME/Let's Encrypt)
    # Optionally configure minimum TLS version
    tls {
        protocols tls1.2 tls1.3
    }

    # Security Headers
    header {
        Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
        Content-Security-Policy "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; style-src 'self'"
        X-Frame-Options "DENY"
        X-Content-Type-Options "nosniff"
        Referrer-Policy "no-referrer"
        Permissions-Policy "accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), interest-cohort=()"

        # Remove server disclosure headers
        -Server
        -X-Powered-By
    }

    # Block access to sensitive files and directories
    @blocked {
        path /.*
        path /vendor/*
        path /config/*
        path /sql/*
        path /tests/*
        path /fuzzing/*
        path /src/*
    }
    respond @blocked 403

    # PHP-FPM
    php_fastcgi unix//run/php/php8.5-fpm.sock {
        root /var/www/pkd-server-php/public
    }

    # Front controller: serve static files, fallback to index.php
    file_server
    try_files {path} {path}/ /index.php?{query}

    # Logging
    log {
        output file /var/log/caddy/demo.publickey.directory-access.log
        format json
    }
}
```

Reload Caddy to enable the site:

```bash
sudo systemctl reload caddy
```

### PHP-FPM Configuration

If you're planning to deploy with PHP FastCGI Process Manager (PHP-FPM), here's an example configuration file:

**File:** `/etc/php/8.5/fpm/pool.d/pkd.conf`

```ini
[pkd]
user = www-data
group = www-data

listen = /run/php/php8.5-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Security settings
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,parse_ini_file,show_source
php_admin_value[expose_php] = Off
php_admin_value[open_basedir] = /var/www/pkd-server-php:/tmp
php_admin_value[session.cookie_httponly] = On
php_admin_value[session.cookie_secure] = On
php_admin_value[session.use_strict_mode] = On

; Logging
php_admin_value[error_log] = /var/log/php/pkd-error.log
php_flag[log_errors] = on
```

## Configuring the Public Key Directory

The first thing you should do is make a `config/local` subdirectory (if one doesn't already exist). Copy the PHP scripts
from `config` to `config/local` that you wish to alter.

Refer to [the configuration section of the technical reference](../reference/configuration.md#configuration-files)) for
the meaning of each file.

### Configuring the Database

The last line of a freshly copied `config/local/database.php` should look like this:

```php
return new EasyDBCache(new PDO('sqlite:' . __DIR__ . '/sqlite.db'));
```

This is a PHP object (an instance of [EasyDB-Cache](https://github.com/paragonie/easydb-cache)) that wraps
[PDO](https://www.php.net/manual/en/book.pdo.php) (an abstraction for multiple database drivers). 

Refer to the PHP manual for configuring your database connection. Generally, you will end up with something like:

```php
$options = []; // optional
return new EasyDBCache(new PDO('mysql:host=localhost;dbname=pkd', 'username', 'password', $options));
```

or:

```php
$options = []; // optional
return new EasyDBCache(new PDO('pgsql:host=localhost;dbname=pkd', 'username', 'password', $options));
```

### Configuring the PKD Parameters

The closing stanza of `config/local/params.php` contains some core settings for your Public Key Directory instance.

```php
return new Params(
    hashAlgo: 'sha256',
    otpMaxLife: 120,
    actorUsername: 'pubkeydir',
    hostname: 'localhost',
    cacheKey: $key,
    httpCacheTtl: 15,
);
```

The `hashAlgo` parameter **MUST NOT** be changed once deployed.

The `otpMaxLife` parameter specifies how long One-Time Passwords are allowed to live.

The `actorUsername` and `hostname` define the actor name that accepts DMs in order to publish Protocol Messages.
The default `pubkeydir` is fine for most setups.

The `hostname` parameter should match the hostname from your virtual host.

The `cacheKey` is used for caching data.

The `httpCacheTtl` parameter is the cache lifetime for HTTP responses. You can tune this as needed. 
Shorter TTLs = fresher responses for clients. Longer TTLs = less load on the HTTP API.