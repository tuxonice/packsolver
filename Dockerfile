FROM php:8.4-cli-alpine

# Define build arguments for user/group IDs with defaults
ARG USER_ID=1001
ARG GROUP_ID=1001

# Install dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    shadow \
    && docker-php-ext-install \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user with dynamic UID/GID
RUN addgroup -g $GROUP_ID appuser && \
    adduser -u $USER_ID -G appuser -s /bin/sh -D appuser

# Set working directory
WORKDIR /app

# Create and set permissions for composer cache directory
RUN mkdir -p /home/appuser/.composer/cache && \
    chown -R appuser:appuser /home/appuser/.composer

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

# Configure git to trust the /app directory
RUN git config --global --add safe.directory /app

# Set the entrypoint
ENTRYPOINT ["php", "bin/packsolver"]
