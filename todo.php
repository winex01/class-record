<?php
/*
TODO:: add filter for clumn that uses DateColumn also in sqlite it doesnt worked
TODO:: add filteter for column starts_at and ends_at or the one that use DateTimeColumn

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
