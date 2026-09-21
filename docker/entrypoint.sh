#!/bin/sh
# ============================================================
# ENTRYPOINT SCRIPT — modagan ni SA MATAG SUGOD sa app container
# ============================================================
# Ang katuyokan: ihanda ang tanan (database, migrations, permissions)
# USA pa magsugod ang PHP-FPM.


# ------------------------------------------------------------
# set -e — "exit on error"
# ------------------------------------------------------------
# Kung adunay command nga mo-fail, mohunong dayon ang script.
# Maayo ni para dili mo-sugod ang app sa broken nga state.
set -e


# ------------------------------------------------------------
# cd ngadto sa project folder sulod sa container
# ------------------------------------------------------------
cd /var/www/html


# ------------------------------------------------------------
# Papason ang karaan nga cache files
# ------------------------------------------------------------
# Ang bootstrap/cache/*.php gitawag og "cached manifests" —
# guna sa imong PC (kung nag-develop ka nga walay Docker) nga
# naka-lista ang dev packages (sama sa laravel/pail).
#
# Kung ma-copy ni ngadto sa container pinaagi sa bind mount,
# mo-crash ang artisan kay wala man ang dev packages didto.
# Busa papason nato — si Laravel mismo mo-regenerate niini.
rm -f bootstrap/cache/services.php bootstrap/cache/packages.php


# ------------------------------------------------------------
# Ihatag sa web server user ang panag-iya sa writable folders
# ------------------------------------------------------------
# Ang www-data maoy user nga mo-run sa PHP-FPM workers.
# Ang bind mount (.:/var/www/html) mag-override sa ownership
# gikan sa image, busa kinahanglan nato ni i-chown dinhi
# SA RUNTIME — dili sa build time.
chown -R www-data:www-data storage bootstrap/cache


# ------------------------------------------------------------
# HUWAIT SA DATABASE — retry loop
# ------------------------------------------------------------
# "until ... do ... done" = balik-balik hangtod mo-succeed.
#
# mysqladmin ping = mangutana sa MySQL: "buhi pa ka?"
#   -h"$DB_HOST"      -> host name ("mysql" = service name sa
#                        docker-compose; Docker DNS mo-resolve niini)
#   -u / -p           -> username ug password (gikan sa environment)
#   --skip-ssl        -> ayaw gamita ang SSL (ang MariaDB client
#                        mo-error sa self-signed cert sa MySQL 8.4)
#   --silent          -> walay output, exit code lang ang importante
#   sleep 2           -> maghuwat og 2 segundos sa dili pa mosulay usab
#
# NOTE: ang "depends_on: service_healthy" sa docker-compose maoy
# unang naghuwat — kini nga loop safety net lang.
until mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --skip-ssl --silent; do
    echo "Waiting for database..."
    sleep 2
done


# ------------------------------------------------------------
# I-run ang MIGRATIONS ug SEEDER
# ------------------------------------------------------------
# migrate = paghimo/update sa mga tables base sa migration files
#   --force = ayaw na pangutana og confirmation (para automated)
php artisan migrate --force

# db:seed = pagbutang og sample data (ang atoang ProductSeeder
#   mogamit og firstOrCreate() — dili mo-duplicate bisan
#   daghan kaayong higayon nga i-restart)
php artisan db:seed --force

# storage:link = paghimo og symlink (shortcut) gikan sa
#   public/storage ngadto sa storage/app/public para
#   ma-access sa web ang mga uploaded files.
#   "|| true" = ayaw i-fail kung naa na ang link.
php artisan storage:link --force 2>/dev/null || true


# ------------------------------------------------------------
# exec "$@" — i-pass ang CMD ngadto sa final command
# ------------------------------------------------------------
# Ang "$@" = tanan nga arguments nga gipasa (mao ang CMD sa
# Dockerfile = "php-fpm").
#
# Ang "exec" mag-REPLACE sa script sa bag-ong process.
# Resulta: ang php-fpm mahimong PID 1 — importante kay si Docker
# mo-send og stop signals (SIGTERM) sa PID 1 para graceful shutdown.
exec "$@"
