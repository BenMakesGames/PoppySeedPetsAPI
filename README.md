# Poppy Seed Pets!

## System Configuration

1. review `.env`; create `.env.local` containing overrides as needed 
2. run `composer install`
3. run `php bin/console doctrine:migrations:migrate`
   * there are no fixtures; you'll need to get recipe, item, NPC data, etc, from somewhere...
4. add to crontab:<br>`* * * * * cd /PATH_TO_POPPY_SEED_PETS && vendor/bin/crunz schedule:run`
