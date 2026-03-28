<?php /*
TODO:: create installation guide a text and a video
 - install herd
 - open herd and add project folder
 - check launch herd on start up

DEPLOY REMINDERS:
env
 -production
 -debug false
 - debugbar false
 - sqlite, mysql if possible
php artisan migrate --step
php artisan icons:cache
npm run build
php artisan optimize
