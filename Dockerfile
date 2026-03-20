FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip nodejs npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
```

---

**2. Create a `.dockerignore` in your project root**
```
node_modules
vendor
.env
.git
```

---

**3. Update Render settings**
- Go to your Render service → **Settings**
- Change **Environment** to `Docker`
- Clear the build command field (the Dockerfile handles it)
- Set **Start Command** to blank (Dockerfile CMD handles it)

---

**4. Add your environment variables on Render**
Go to **Environment** tab on Render and add your `.env` values:
```
APP_KEY=your_app_key
APP_ENV=production
APP_URL=https://your-render-url.onrender.com
DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...