#!/bin/bash

# PlayPulse Panel Setup Script
# This script automates the installation and configuration of PlayPulse Panel

set -e

echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║                            PlayPulse Panel Setup                             ║"
echo "║                    Game Server Hosting Control Panel                        ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        log_error "This script should not be run as root for security reasons."
        log_info "Please run as a regular user with sudo privileges."
        exit 1
    fi
}

# Check system requirements
check_requirements() {
    log_info "Checking system requirements..."
    
    # Check OS
    if [[ ! -f /etc/os-release ]]; then
        log_error "Unable to determine operating system."
        exit 1
    fi
    
    source /etc/os-release
    if [[ "$ID" != "ubuntu" ]] && [[ "$ID" != "debian" ]]; then
        log_warning "This script is designed for Ubuntu/Debian. Other distributions may not work properly."
    fi
    
    # Check required commands
    local required_commands=("curl" "wget" "git" "docker" "docker-compose")
    for cmd in "${required_commands[@]}"; do
        if ! command -v "$cmd" &> /dev/null; then
            log_error "$cmd is required but not installed."
            log_info "Please install $cmd and run this script again."
            exit 1
        fi
    done
    
    log_success "System requirements check passed."
}

# Install Docker if not present
install_docker() {
    if ! command -v docker &> /dev/null; then
        log_info "Installing Docker..."
        curl -fsSL https://get.docker.com -o get-docker.sh
        sudo sh get-docker.sh
        sudo usermod -aG docker $USER
        rm get-docker.sh
        log_success "Docker installed successfully."
        log_warning "Please log out and log back in for Docker group changes to take effect."
    else
        log_info "Docker is already installed."
    fi
}

# Install Docker Compose if not present
install_docker_compose() {
    if ! command -v docker-compose &> /dev/null; then
        log_info "Installing Docker Compose..."
        sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
        sudo chmod +x /usr/local/bin/docker-compose
        log_success "Docker Compose installed successfully."
    else
        log_info "Docker Compose is already installed."
    fi
}

# Create directory structure
create_directories() {
    log_info "Creating directory structure..."
    
    local directories=(
        "data/mysql"
        "data/redis"
        "logs/nginx"
        "logs/php"
        "backups"
        "uploads"
    )
    
    for dir in "${directories[@]}"; do
        mkdir -p "$dir"
    done
    
    log_success "Directory structure created."
}

# Generate secure passwords
generate_passwords() {
    log_info "Generating secure passwords..."
    
    DB_PASSWORD=$(openssl rand -base64 32)
    DB_ROOT_PASSWORD=$(openssl rand -base64 32)
    REDIS_PASSWORD=$(openssl rand -base64 32)
    APP_KEY="base64:$(openssl rand -base64 32)"
    JWT_SECRET=$(openssl rand -base64 64)
    
    log_success "Secure passwords generated."
}

# Create environment file
create_env_file() {
    log_info "Creating environment configuration..."
    
    # Get user input for basic configuration
    read -p "Enter your domain name (e.g., panel.yourdomain.com): " DOMAIN_NAME
    read -p "Enter your email address: " ADMIN_EMAIL
    read -p "Enter admin username: " ADMIN_USERNAME
    read -s -p "Enter admin password: " ADMIN_PASSWORD
    echo ""
    
    # Create .env file
    cat > .env << EOF
# Application Configuration
APP_NAME="PlayPulse Panel"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN_NAME}
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=playpulse
DB_USERNAME=playpulse
DB_PASSWORD=${DB_PASSWORD}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}

# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=${ADMIN_EMAIL}
MAIL_FROM_NAME="PlayPulse Panel"

# JWT Configuration
JWT_SECRET=${JWT_SECRET}
JWT_TTL=60
JWT_REFRESH_TTL=20160

# File Storage
FILESYSTEM_DISK=local
# For S3 storage, uncomment and configure:
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=

# Payment Gateways (Optional)
STRIPE_KEY=
STRIPE_SECRET=
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=

# Admin Account
ADMIN_EMAIL=${ADMIN_EMAIL}
ADMIN_USERNAME=${ADMIN_USERNAME}
ADMIN_PASSWORD=${ADMIN_PASSWORD}

# Security
BCRYPT_ROUNDS=12

# Monitoring
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
EOF

    log_success "Environment file created."
}

# Update Docker Compose with generated passwords
update_docker_compose() {
    log_info "Updating Docker Compose configuration..."
    
    # Update docker-compose.yml with generated passwords
    sed -i "s/secure_password/${DB_PASSWORD}/g" docker-compose.yml
    sed -i "s/root_password/${DB_ROOT_PASSWORD}/g" docker-compose.yml
    
    log_success "Docker Compose configuration updated."
}

# Setup SSL certificate with Let's Encrypt
setup_ssl() {
    read -p "Do you want to set up SSL with Let's Encrypt? (y/n): " setup_ssl_choice
    
    if [[ "$setup_ssl_choice" == "y" || "$setup_ssl_choice" == "Y" ]]; then
        log_info "Setting up SSL certificate..."
        
        # Install certbot if not present
        if ! command -v certbot &> /dev/null; then
            sudo apt-get update
            sudo apt-get install -y certbot python3-certbot-nginx
        fi
        
        # Generate certificate
        sudo certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos --email "$ADMIN_EMAIL"
        
        log_success "SSL certificate configured."
    else
        log_info "Skipping SSL setup. You can configure it later."
    fi
}

# Start services
start_services() {
    log_info "Starting PlayPulse Panel services..."
    
    # Pull latest images
    docker-compose pull
    
    # Start services
    docker-compose up -d
    
    # Wait for database to be ready
    log_info "Waiting for database to be ready..."
    sleep 30
    
    # Run migrations and seed database
    docker-compose exec -T app php artisan migrate --force
    docker-compose exec -T app php artisan db:seed --force
    
    # Create admin user
    docker-compose exec -T app php artisan make:admin \
        --email="$ADMIN_EMAIL" \
        --username="$ADMIN_USERNAME" \
        --password="$ADMIN_PASSWORD"
    
    # Optimize application
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    
    log_success "PlayPulse Panel is now running!"
}

# Display final information
show_completion_info() {
    echo ""
    echo "╔══════════════════════════════════════════════════════════════════════════════╗"
    echo "║                         Installation Complete!                               ║"
    echo "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo ""
    log_success "PlayPulse Panel has been successfully installed and configured."
    echo ""
    echo "Access Information:"
    echo "  • Panel URL: https://$DOMAIN_NAME"
    echo "  • Admin Username: $ADMIN_USERNAME"
    echo "  • Admin Email: $ADMIN_EMAIL"
    echo ""
    echo "Important Files:"
    echo "  • Configuration: .env"
    echo "  • Docker Compose: docker-compose.yml"
    echo "  • Logs: logs/"
    echo "  • Data: data/"
    echo ""
    echo "Useful Commands:"
    echo "  • View logs: docker-compose logs -f"
    echo "  • Restart services: docker-compose restart"
    echo "  • Stop services: docker-compose down"
    echo "  • Update panel: git pull && docker-compose up --build -d"
    echo ""
    echo "Next Steps:"
    echo "  1. Configure your DNS to point to this server"
    echo "  2. Log in to the admin panel and configure payment gateways"
    echo "  3. Add server nodes and game templates"
    echo "  4. Create your first billing plans"
    echo ""
    log_info "For support and documentation, visit: https://docs.playpulse.com"
    echo ""
}

# Main installation process
main() {
    echo "Starting PlayPulse Panel installation..."
    echo ""
    
    check_root
    check_requirements
    install_docker
    install_docker_compose
    create_directories
    generate_passwords
    create_env_file
    update_docker_compose
    setup_ssl
    start_services
    show_completion_info
    
    log_success "Installation completed successfully!"
}

# Handle script interruption
trap 'log_error "Installation interrupted. Please run the script again to complete setup."; exit 1' INT TERM

# Run main function
main "$@"