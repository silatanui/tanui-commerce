# Tanui Commerce

Multi-store ecommerce demonstration for a software portfolio. Four branded storefronts share one Laravel application, one MySQL database and one administration dashboard. Checkout is simulated: the application does not collect card details, take payment, send transactional email or deliver products.

## Stores

- Tanui Fashion: `/store/fashion`
- Tanui Electronics: `/store/electronics`
- Tanui Home: `/store/home`
- Tanui Market: `/store/market`
- Administration: `/admin`

## Local setup

The project is intended to run under XAMPP.

1. Create the MySQL database `tanui_commerce`.
2. Copy `.env.example` to `.env` if needed and set the MySQL connection.
3. Install dependencies and prepare the app:

```bash
C:\xampp\php\php.exe C:\xampp\php\composer.phar install
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan storage:link
```

Open `http://localhost/Tanui_commerce/public` or run `php artisan serve`.

## Demonstration accounts

| Role | Email | Password |
|---|---|---|
| Administrator | admin@tanuicommerce.test | password |
| Customer | amina@example.test | password |
| Customer | david@example.test | password |

Sample coupon codes: `ATELIER10`, `VOLT5`, `HABITA`, `BASKET10`.

## Safeguards

- `DEMO_ALLOW_PAYMENTS` cannot turn on a gateway. Card fields are rejected.
- Order records are forced to `is_demo` and `payment_status = not_processed`.
- Prices are read from the database during cart updates and checkout.
- Queries and policies keep each store's catalogue and orders separate.
- Only an authenticated administrator can restore the seeded data, and the reset form requires the confirmation phrase `RESET DEMONSTRATION`.

Set `APP_DEBUG=false` before a public deployment.
