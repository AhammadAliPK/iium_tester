# iium_tester

IIUM Test Case Management System — A comprehensive testing platform built with Laravel 13 and Livewire 4, featuring the IIUM Digital Design Language System.

## Development Setup

### Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup (SQLite - default):**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

   The system includes a default admin user:
   - Email: `admin@iium.edu.my`
   - Password: `password`

4. **Run development servers:**
   ```bash
   # Terminal 1: Laravel backend
   composer run dev
   # or
   php artisan serve

   # Terminal 2: Frontend build
   npm run dev
   ```

5. **Access the application:**
   - Navigate to `http://localhost:8000`
   - Login with the seeded admin credentials

### MySQL Setup (MAMP Alternative)

To use MySQL instead of SQLite, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iium_tester
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

Then run:
```bash
php artisan migrate --seed
```

## Architecture

This application follows the IIUM Test Case Management System architecture:
- **Server-rendered MVC monolith** with Livewire 4 islands
- **Session authentication** (httpOnly cookies)
- **IIUM Digital Design Language** branding
- **Portable migrations** supporting SQLite and MySQL 8.x

## Tech Stack

- PHP 8.3+
- Laravel 13.x
- Livewire 4.x
- Tailwind CSS 4.x
- Alpine.js 3.x (included with Livewire)

## Design System

The application uses the IIUM Digital Design Language System (DLS) with:
- Brand colors: Turquoise (#00928F), Gold (#D59F0F)
- Typography: Roboto Slab (headings), Inter (UI), Barlow Condensed (labels)
- Light/dark theme support with localStorage persistence
- Responsive design with mobile-first approach

## Testing

Run the test suite:
```bash
php artisan test
```

## Build for Production

```bash
npm run build
```

## License

© 2026 International Islamic University Malaysia — Digital Identity Standard
