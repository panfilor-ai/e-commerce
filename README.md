# E-Commerce (Laravel + Docker)

A basic e-commerce app built with Laravel 13: product catalog, session-based cart, checkout, and orders. Ships with a Docker setup including MySQL and phpMyAdmin.

## Stack

- **Laravel 13** (PHP 8.3, php-fpm)
- **Nginx** — web server
- **MySQL 8.4** — database
- **phpMyAdmin** — database UI

## Quick Start (Docker)

```bash
docker compose up -d --build
```

On first start the app container waits for MySQL, runs migrations, and seeds sample products automatically.

| Service     | URL                          |
| ----------- | ---------------------------- |
| App         | http://localhost:8000        |
| phpMyAdmin  | http://localhost:8080        |

**phpMyAdmin login:** server `mysql`, user `root`, password `root` (or user `laravel` / `secret`).

### Database credentials

Defined in `docker-compose.yml`:

- Database: `ecommerce`
- User: `laravel` / Password: `secret`
- Root password: `root`

### Useful commands

```bash
# Run artisan commands inside the app container
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan tinker

# View logs
docker compose logs -f app

# Stop everything
docker compose down

# Stop and delete database data
docker compose down -v
```

## Local Development (without Docker)

Requires PHP 8.3+ and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed   # uses SQLite by default
php artisan serve
```

App runs at http://localhost:8000.

## Features

- Product listing & detail pages
- Session-based cart (add / update quantity / remove)
- Checkout form with validation
- Orders & order items stored in the database (stock decremented on purchase)
- Sample product seeder (idempotent — safe to re-run)

## Project Structure

```
app/Http/Controllers/   Product, Cart, Checkout controllers
app/Models/             Product, Order, OrderItem
resources/views/        Blade templates (products, cart, checkout)
database/migrations/    products, orders, order_items tables
database/seeders/       ProductSeeder with sample data
docker/                 Nginx config + app entrypoint
docker-compose.yml      app, nginx, mysql, phpmyadmin services
Dockerfile              PHP 8.3-fpm app image
```

---

# Pagsabot sa Docker (Para sa mga Students)

## Unsa ang Docker?

Ang Docker usa ka tool nga mo-putos sa imong application UG ang tanan
nga gikinahanglan niini (PHP, MySQL, Nginx, libraries) sulod sa usa ka
**container** — murag mini-computer nga isolated gikan sa imong PC.

**Ang problema nga gi-solve niini:**
> "Nag-work sa akong PC, pero dili sa imong PC!"

Sa Docker, parehas gyud ang environment sa tanan — bisan Windows, Mac,
o Linux ang gamit sa imong classmate.

## Mga Key Concepts

```
IMAGE       = ang "recipe" o template (gihimo sa Dockerfile)
CONTAINER   = ang nagdagan nga instance sa image (ang actual nga app)
VOLUME      = persistent storage (data nga dili mawala)
NETWORK     = internal nga komunikasyon sa mga containers
PORT        = agianan sa requests (host:container = 8000:80)
```

### Analogy

```
Dockerfile   = blueprint sa balay
Image        = ang plano nga gi-print na (ready na i-build)
Container    = ang tinuod nga balay nga gitukod gikan sa plano
docker-compose.yml = master plan sa tibuok subdivision (4 ka balay)
```

## Ang Atoang 4 ka Containers

```
  Browser
     │
     ▼
┌─────────┐  port 8000
│  NGINX  │  ← modawat sa requests, mo-serve og static files
└────┬────┘
     │ FastCGI (port 9000)
     ▼
┌─────────┐
│   APP   │  ← PHP-FPM, dinhi modagan ang Laravel code
└────┬────┘
     │ SQL queries (port 3306)
     ▼
┌─────────┐         ┌─────────────┐  port 8080
│  MYSQL  │ ◄───────│ PHPMYADMIN  │  ← web UI para sa database
└─────────┘         └─────────────┘
```

- **nginx** — ang "receptionist": modawat sa HTTP requests
- **app** — ang "worker": mo-process sa PHP/Laravel logic
- **mysql** — ang "filing cabinet": dinhi gitago ang data
- **phpmyadmin** — ang "bintana" sa filing cabinet

## Giunsa Pagdagan

```bash
docker compose up -d --build
```

**Unsa ang nahitabo sa background:**

1. Gi-build ang `app` image gikan sa `Dockerfile`
2. Gi-download ang `nginx`, `mysql`, ug `phpmyadmin` images
3. Gibuhat ang internal network ug volumes
4. Misugod ang `mysql`, gipaabot nga mo-healthy
5. Misugod ang `app` → ang `entrypoint.sh`:
   - naghuwat sa MySQL (`mysqladmin ping`)
   - mo-run og `php artisan migrate` (paghimo og tables)
   - mo-run og `php artisan db:seed` (sample products)
   - mo-sugod sa PHP-FPM
6. Misugod ang `nginx` ug `phpmyadmin`

## Ngano Naay 3 ka Places nga Naa ang "Wait" sa Database?

| Level | Mechanism | Katuyokan |
|-------|-----------|-----------|
| Compose | `depends_on: service_healthy` | Dili mo-sugod ang app container hangtod healthy ang mysql |
| Healthcheck | `mysqladmin ping` (matag 5s) | Si Docker mismo mo-verify nga andam na ang MySQL |
| Entrypoint | `until mysqladmin ping ...` | Safety net sulod sa app container |

## Bind Mount vs Named Volume

```yaml
volumes:
  - .:/var/www/html                  # BIND MOUNT
  - vendor_data:/var/www/html/vendor # NAMED VOLUME
```

- **Bind mount** (`.:/var/www/html`) — ang folder sa imong PC
  gi-attach diretso sa container. Edit ka sa VS Code → dayon
  makita sa container. Para ni sa development.
- **Named volume** (`vendor_data`) — gi-manage ni Docker.
  Gigamit para sa `vendor/` kay kung bind mount lang,
  "matabunan" sa walay sulod nga vendor sa imong PC ang
  vendor nga na-install sulod sa image.

## Mga Essentials nga Commands

```bash
# Pang-adlaw-adlaw
docker compose up -d          # sugdan ang tanan (background)
docker compose down           # hunongon ang tanan
docker compose ps             # status sa mga containers
docker compose logs -f app    # tan-awon ang logs (live)

# Sulod sa container
docker compose exec app bash              # mosulod sa app container
docker compose exec app php artisan tinker # Laravel REPL

# Debugging
docker compose logs mysql     # logs sa database
docker compose restart nginx  # i-restart ang nginx lang

# Limpyo
docker compose down -v        # hunong + papason ang volumes (fresh start)
docker system prune           # papason ang unused images/containers
```

## Flow sa Usa ka Request

```
1. Browser: GET http://localhost:8000/products/1
2. Nginx (:8000 → :80): walay file nga "products/1"
   → try_files → i-forward sa /index.php
3. Nginx → PHP-FPM (app:9000): i-execute ang index.php
4. Laravel: mo-match sa route → ProductController@show
5. Laravel → MySQL (mysql:3306): SELECT * FROM products WHERE id=1
6. Laravel: mo-render sa Blade view → HTML
7. Nginx → Browser: ipakita ang page
```

## Common nga Problema ug Solusyon

| Sintomas | Hinungdan | Solusyon |
|----------|-----------|----------|
| 502 Bad Gateway | PHP-FPM wala pa mo-sugod / app stuck | `docker compose logs app` |
| 500 error | Permissions sa `storage/` | Gi-fix na sa entrypoint (chown) |
| "Connection refused" sa DB | MySQL nag-initialize pa | Huwata lang, o `docker compose logs mysql` |
| Port already in use | Laing app naggamit sa 8000/8080 | Usba ang port sa docker-compose.yml |
| Karaang data | Volume nag-keep sa old DB | `docker compose down -v` |

## Arbeles nga mga File (Tan-awa ang comments!)

Ang tanan nga Docker files naay Cebuano nga comments sa matag
linya — basaha sila para mas masabtan:

- `Dockerfile` — giunsa pag-build ang app image
- `docker/entrypoint.sh` — giunsa pag-prepare sa container
- `docker/nginx/default.conf` — giunsa pag-route sa requests
- `docker-compose.yml` — giunsa pag-coordinate sa 4 ka services

