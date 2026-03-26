<?php
/*
// TODO:: check mysql query and convert it to sqlite
 Search your codebase for common MySQL-only syntax
Things to grep for:
bash# Raw MySQL functions
grep -r "GROUP_CONCAT" app/
grep -r "JSON_EXTRACT\|JSON_CONTAINS" app/
grep -r "REGEXP" app/
grep -r "STR_TO_DATE\|DATE_FORMAT" app/
grep -r "IF(" app/
grep -r "FIELD(" app/
grep -r "->selectRaw\|->whereRaw\|->orderByRaw\|->groupByRaw\|->havingRaw" app/

TODO:: check all table search if there is an error, including the modals and tables.
TODO:: check all tables column sortable/order


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
