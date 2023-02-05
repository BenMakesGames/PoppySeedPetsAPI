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

