FROM php:8.3-fpm

# Install system dependencies + Python
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpq-dev \
    python3 python3-pip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Create necessary directories
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Install Python dependencies for PDF parsing
RUN pip3 install pdfplumber --break-system-packages

RUN cp .env.example .env && php artisan key:generate --force

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD php artisan config:clear && \
    php artisan config:cache && \
    php artisan db:seed --force && \
    php artisan tinker --execute='DB::statement("ALTER TABLE exams ADD COLUMN IF NOT EXISTS uploaded_by BIGINT REFERENCES users(id) ON DELETE SET NULL;"); DB::statement("CREATE TABLE IF NOT EXISTS teacher_notes (id BIGSERIAL PRIMARY KEY, teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE, semester_id BIGINT REFERENCES semesters(id) ON DELETE SET NULL, status VARCHAR(255) NOT NULL DEFAULT '\''no_status'\'', notes TEXT, updated_by BIGINT REFERENCES users(id) ON DELETE SET NULL, created_at TIMESTAMP(0), updated_at TIMESTAMP(0), CONSTRAINT teacher_notes_teacher_id_semester_id_unique UNIQUE (teacher_id, semester_id));"); echo "Schema updates applied.";' && \
    php artisan serve --host=0.0.0.0 --port=8000