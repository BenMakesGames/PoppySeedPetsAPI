# Poppy Seed Pets!

## Install & Configure

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
