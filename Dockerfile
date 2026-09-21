# ============================================================
# DOCKERFILE — Ang "recipe" sa paghimo sa atoang app image
# ============================================================
# Ang Dockerfile maoy lista sa instructions nga gisunod ni Docker
# para mo-build og image. Ang image murag "template" — gikan niini
# makahimo ta og mga container (nga maoy nagdagan nga instance).
#
# Hunong-hunonga: ang image = recipe sa pagkaon,
#                 ang container = ang actual nga pagkaon nga giluto.


# ------------------------------------------------------------
# FROM — base image / sugdan nga image
# ------------------------------------------------------------
# Magsugod ta gikan sa opisyal nga PHP 8.3 image nga naay
# PHP-FPM (FastCGI Process Manager) preinstalled.
#
# Ngano "fpm" ug dili apache/cli?
#   - Ang Nginx maoy modawat sa HTTP requests
#   - Dayon i-pass niya ang PHP requests ngadto sa PHP-FPM (port 9000)
#   - Kini nga setup mas kusgan ug mas scalable kaysa Apache
FROM php:8.3-fpm


# ------------------------------------------------------------
# RUN — modagan og commands SA PANAHON SA BUILD (dili runtime)
# ------------------------------------------------------------
# Mga package nga gikinahanglan:
#
#   apt-get update          -> i-update ang lista sa available packages
#   git, unzip              -> gikinahanglan ni Composer para mo-download
#                              ug mo-extract sa mga packages
#   libzip-dev              -> para sa "zip" PHP extension
#   libpng-dev              -> para sa image handling (pictures)
#   libonig-dev             -> para sa "mbstring" (multibyte strings —
#                              importante sa UTF-8/Unicode text)
#   libxml2-dev             -> para sa XML parsing
#   default-mysql-client    -> mysql/mysqladmin CLI tools —
#                              gamiton sa entrypoint para mag-check
#                              kung andam na ba ang database
#
#   docker-php-ext-install  -> helper script sa php image para
#                              mo-compile ug mo-enable sa PHP extensions:
#     pdo_mysql -> mao ni ang connector sa Laravel ngadto sa MySQL
#     mbstring  -> string functions nga mo-work sa Unicode
#     zip       -> unzip sa mga composer packages
#     bcmath    -> tukma nga math (importante sa presyo/kwarta!)
#
#   rm -rf /var/lib/apt/lists/*
#                 -> papason ang apt cache para di kadako ang image.
#                    TIP: gagmay nga image = paspas nga download/deploy
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
        default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*


# ------------------------------------------------------------
# COPY --from= — kuhaon ang file gikan sa LAIN nga image
# ------------------------------------------------------------
# Gitawag ni "multi-stage build". Ang "composer:2" usa ka opisyal
# nga image nga naay Composer. Imbis nga mag-install ta og Composer
# manually, kuhaon lang nato ang binary gikan didto.
#
# Ang Composer = package manager sa PHP (murag npm sa JavaScript).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ------------------------------------------------------------
# WORKDIR — itakda ang "working directory"
# ------------------------------------------------------------
# Murag "cd" — tanan nga sunod nga commands ug ang container
# mismo modagan sulod sa /var/www/html (standard nga web folder).
WORKDIR /var/www/html


# ------------------------------------------------------------
# COPY — i-copy ang files gikan sa imong PC ngadto sa image
# ------------------------------------------------------------
# IMPORTANTENG TECHNIQUE: giuna nato ang composer.json/composer.lock
# USA pa i-copy ang tanan nga code.
#
# Ngano? Ang Docker mo-cache sa matag layer. Kung wala mausab ang
# composer.json/lock, dili na usbon ang "composer install" —
# dagko kaayog tipig sa oras sa matag rebuild!
COPY composer.json composer.lock ./

# I-install ang tanan nga PHP dependencies (laravel/framework, etc.)
#   --no-dev        -> dili i-install ang dev-only packages
#                      (mas gagmay ug mas secure ang image)
#   --no-scripts    -> ayaw dagana ang artisan scripts karon
#                      (kay wala pa ang code)
#   --no-autoloader -> ayaw pa generate og autoload (unaha lang)
#   --prefer-dist   -> download og zip kaysa git clone (mas paspas)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist


# ------------------------------------------------------------
# COPY . . — i-copy na karon ang TANAN nga project files
# ------------------------------------------------------------
# Ang unang "." = ang folder diin naa ang Dockerfile (imong PC)
# Ang ikaduhang "." = ang WORKDIR sulod sa image (/var/www/html)
COPY . .


# ------------------------------------------------------------
# RUN — final setup human ma-copy ang code
# ------------------------------------------------------------
# composer dump-autoload --optimize
#     -> himuon ang optimized nga autoload map (mas paspas)
# chown -R www-data:www-data storage bootstrap/cache
#     -> i-hatag sa web server user (www-data) ang panag-iya sa
#        storage/ ug bootstrap/cache/ para makasulat si Laravel
#        og logs, sessions, ug compiled views
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache


# ------------------------------------------------------------
# COPY ang startup script ngadto sa PATH sa container
# ------------------------------------------------------------
# Ang /usr/local/bin naa sa PATH — meaning matawag siya
# bisan asa nga directory.
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

# chmod +x = himuon siyang "executable" (pwede na daganon)
RUN chmod +x /usr/local/bin/app-entrypoint


# ------------------------------------------------------------
# ENTRYPOINT vs CMD — duha ka butang nga magkalain!
# ------------------------------------------------------------
# ENTRYPOINT = command nga SEMPRE modagan pag-sugod sa container.
#              Ang atoang script mo-wait sa MySQL, mo-run og
#              migrations ug seeder.
#
# CMD = default nga argumento sa ENTRYPOINT. Kung modagan ka og
#       "docker run <image> <lain nga command>", ang CMD
#       ma-override — pero ang ENTRYPOINT magpabilin.
ENTRYPOINT ["app-entrypoint"]

# Ang default: i-run ang PHP-FPM sa foreground para
# magpabilin nga buhi ang container (kung mo-exit ni,
# mohunong pod ang container).
CMD ["php-fpm"]
