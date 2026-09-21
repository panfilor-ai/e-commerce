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
