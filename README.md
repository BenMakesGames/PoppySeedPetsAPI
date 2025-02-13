# Poppy Seed Pets!

## Install & Configure

### PHP

Install the following linux packages:
* php8.3
* php8.3-bcmath
* php8.3-cli
* php8.3-common
* php8.3-devel
* php8.3-fpm
* php8.3-gd
* php8.3-gmp
* php8.3-intl
* php8.3-mbstring
* php8.3-mysqlnd
* php8.3-opcache
* php8.3-pdo
* php8.3-process
* php8.3-sodium
* php8.3-xml
* php8.3-zip
* php-pear
* composer
* redis6-devel
* lz4-devel

With pecl, install:
* igbinary
* msgpack
* zstd
* lzf
* redis, and answer "yes" to all questions about igbinary, msgpack, zstd, and lzf

Make sure to enable these various modules in a PHP ini file; examples (from https://github.com/amazonlinux/amazon-linux-2023/issues/328):
* `/etc/php.d/30-igbinary.ini` with `extension=igbinary.so`
  * Exact path may vary depending on your system
* 30-msgpack for `msgpack.so`
* 40-zstd for `zstd.so`
* 40-lzf for `lzf.so`
* 41-redis for `redis.so`

### Apache

Create a `.conf` file for Poppy Seed Pets, for example `/etc/httpd/conf.d/poppyseedpets.conf` (again, exact paths may vary depending on your system):

```apache
DocumentRoot "/var/www/html/PoppySeedPetsAPI/public"

<Directory "/var/www">
    AllowOverride None
    Require all granted
</Directory>

<Directory "/var/www/html/PoppySeedPetsAPI/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Poppy Seed Pets, itself

1. review `.env`; create `.env.local` containing overrides as needed 
2. run `composer install`
3. run `php bin/console doctrine:migrations:migrate`
   * there are no fixtures; you'll need to get recipe, item, NPC data, etc, from somewhere...
4. add to crontab:<br>`* * * * * cd /PATH_TO_POPPY_SEED_PETS && vendor/bin/crunz schedule:run`

## Local Dev

### Running

1. run `symfony server:start` in root of this project
2. run `ng serve` in root of web app project
3. optionally, start local redis service

### Resetting Accounts for Local Login

```sql
/* change all user email addresses to <id>@poppyseedpets.com */
UPDATE user SET email=CONCAT(id, '@poppyseedpets.com') WHERE email NOT LIKE '%@poppyseedpets.com';
```

## Other Config Considerations

### Brotli

After the brotli mod is installed and enabled, use these rules to compress API responses (`application/json`):

```
<IfModule mod_brotli.c>
    AddOutputFilterByType BROTLI_COMPRESS application/json

    BrotliCompressionQuality 6
    BrotliCompressionWindow 19
    BrotliCompressionMaxInputBlock 18

    Header append Vary Accept-Encoding env=!dont-vary
</IfModule>
```

The largest API calls happen when the player views their house (pets & items). I was seeing requests that rarely even get to 200KB, but I'm sure some whacky user has many hundreds of items and maybe gets to 1MB.

* BrotliCompressionWindow of 19 = 512KB
* BrotliCompressionMaxInputBlock of 18 = 256KB

We could probably go even lower, but these values are already smaller (less RAM-using) than typical settings that "balance for performance", so I'm sure it's fine :P

The `Header append Vary Accept-Encoding` setting is because the API sits behind the AWS load balancer - a proxy - and we need to tell that proxy that the `Accept-Encoding` header is important information for us, and to please pass it along.
