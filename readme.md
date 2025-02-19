# TashiCell Online PMS
To run, download from repository, remove vendor folder and then do a composer update. This downloads all dependencies.

Then clear cache:
1. php artisan config:clear
2. php artisan cache:clear
3. php artisan config:cache

Optionally, clear cached views
 - php artisan view:clear
 
To run queue listener, run following command
 - php artisan queue:listen --tries=10

Run supervisor (install on linux) to restart above process if dead