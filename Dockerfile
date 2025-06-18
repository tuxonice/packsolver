FROM php:8.4-cli-alpine

# Install dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN addgroup -g 1000 appuser && \
    adduser -u 1000 -G appuser -s /bin/sh -D appuser

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install dependencies as root (for better performance and permissions)
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Generate optimized autoloader as root
RUN composer dump-autoload --optimize

# Set executable permissions for the bin file
RUN chmod +x bin/packsolver

# Set proper ownership for all files after all operations are complete
RUN chown -R appuser:appuser /app

# Switch to non-root user for security when running the container
USER appuser

# Set the entrypoint
ENTRYPOINT ["php", "bin/packsolver"]
