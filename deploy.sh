#!/bin/bash

# SmartYonetim Server Deployment Script
# This script helps deploy the SmartYonetim application to a PHP server

set -e

echo "🚀 SmartYonetim Server Deployment Script"
echo "========================================"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as web server user (optional)
check_user() {
    if [ "$EUID" -eq 0 ]; then
        print_warning "Running as root. Consider switching to web server user (www-data, apache, etc.)"
    fi
}

# Check PHP version
check_php() {
    print_status "Checking PHP version..."
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed or not in PATH"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
    PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")
    
    print_status "Found PHP version: $PHP_VERSION"
    
    if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
        print_error "PHP 8.2 or higher is required. Current version: $PHP_VERSION"
        exit 1
    fi
}

# Check PHP extensions
check_extensions() {
    print_status "Checking required PHP extensions..."
    
    required_extensions=("pdo" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "bcmath" "curl" "fileinfo")
    missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if ! php -m | grep -i "$ext" > /dev/null; then
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -ne 0 ]; then
        print_error "Missing required PHP extensions: ${missing_extensions[*]}"
        print_error "Please install these extensions and try again"
        exit 1
    fi
    
    print_status "All required PHP extensions are installed"
}

# Check Composer
check_composer() {
    print_status "Checking Composer..."
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed. Please install Composer first:"
        print_error "curl -sS https://getcomposer.org/installer | php"
        print_error "sudo mv composer.phar /usr/local/bin/composer"
        exit 1
    fi
    
    print_status "Composer is installed"
}

# Install dependencies
install_dependencies() {
    print_status "Installing PHP dependencies..."
    composer install --optimize-autoloader --no-dev --no-interaction
    
    if [ $? -ne 0 ]; then
        print_error "Failed to install PHP dependencies"
        exit 1
    fi
}

# Setup environment file
setup_environment() {
    print_status "Setting up environment file..."
    
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            cp .env.production .env
            print_status "Copied .env.production to .env"
        elif [ -f ".env.example" ]; then
            cp .env.example .env
            print_status "Copied .env.example to .env"
        else
            print_error "No environment template found"
            exit 1
        fi
        
        print_warning "Don't forget to update .env file with your database and other configurations!"
    else
        print_status ".env file already exists"
    fi
}

# Generate application key
generate_key() {
    print_status "Generating application key..."
    php artisan key:generate --force
}

# Setup database
setup_database() {
    print_status "Setting up database..."
    
    # Check if database connection works
    if php artisan migrate:status > /dev/null 2>&1; then
        print_status "Database connection successful"
        
        read -p "Do you want to run database migrations? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan migrate --force
            print_status "Database migrations completed"
        fi
    else
        print_warning "Database connection failed. Please check your .env file database settings"
        print_warning "You can run 'php artisan migrate' manually after fixing the database configuration"
    fi
}

# Setup storage and cache
setup_storage() {
    print_status "Setting up storage directories..."
    
    # Create storage directories if they don't exist
    mkdir -p storage/logs
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p bootstrap/cache
    
    # Set permissions (adjust based on your server setup)
    chmod -R 755 storage
    chmod -R 755 bootstrap/cache
    
    print_status "Storage directories created"
}

# Optimize for production
optimize_app() {
    print_status "Optimizing application for production..."
    
    # Clear any existing cache
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    # Cache configurations
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    print_status "Application optimized"
}

# Build frontend assets (if Node.js is available)
build_assets() {
    if command -v npm &> /dev/null; then
        print_status "Building frontend assets..."
        
        if [ -f "package.json" ]; then
            npm install --production
            npm run build
            print_status "Frontend assets built"
        fi
    else
        print_warning "Node.js/NPM not found. Skipping asset build."
        print_warning "You may need to build assets manually if your application requires them."
    fi
}

# Set final permissions
set_permissions() {
    print_status "Setting final permissions..."
    
    # Set appropriate permissions for web server
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # Make artisan executable
    chmod +x artisan
    
    # Set special permissions for storage and bootstrap/cache
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    
    print_status "Permissions set"
}

# Main deployment function
deploy() {
    print_status "Starting SmartYonetim deployment..."
    
    check_user
    check_php
    check_extensions
    check_composer
    install_dependencies
    setup_environment
    generate_key
    setup_storage
    setup_database
    build_assets
    optimize_app
    set_permissions
    
    print_status "✅ Deployment completed successfully!"
    echo
    print_status "Next steps:"
    echo "1. Configure your web server to point to the 'public' directory"
    echo "2. Update your .env file with correct database and mail settings"
    echo "3. Set up SSL certificate for HTTPS"
    echo "4. Configure your payment gateway credentials"
    echo "5. Test the application"
    echo
    print_status "🎉 SmartYonetim is ready to use!"
}

# Run deployment
deploy