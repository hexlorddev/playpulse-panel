# PlayPulse - Game Server Hosting Panel

**PlayPulse** is a comprehensive, production-ready game server hosting control panel built with Laravel and modern web technologies. It provides a powerful yet user-friendly interface for managing game servers, with support for multiple games, automated deployments, file management, and billing integration.

## 🚀 Features

### 🎮 Server Management
- **Multi-Game Support**: Minecraft (Vanilla, Paper, Spigot, Forge, Fabric), Source Games (CS:GO, CS2, TF2, Garry's Mod), Rust, ARK, Terraria, Valheim, and more
- **One-Click Deployment**: Instant server creation with pre-configured templates
- **Real-Time Monitoring**: Live CPU, RAM, disk, and network usage tracking
- **Server Controls**: Start, stop, restart, and kill server operations
- **Console Access**: Full web-based console with command execution
- **Automated Management**: Crash detection, auto-restart, and health monitoring

### 📁 File Management
- **Web-Based File Manager**: Complete file system access through the browser
- **Code Editor**: Syntax-highlighted editor for configuration files
- **File Operations**: Upload, download, compress, extract, and bulk operations
- **Permission Management**: File permission controls and ownership
- **Configuration Templates**: Auto-generated config files for different games
- **Version Control**: File change tracking and rollback functionality

### 💾 Backup System
- **Automated Backups**: Scheduled and manual backup creation
- **Multiple Storage**: Local, AWS S3, and cloud storage support
- **Incremental Backups**: Efficient storage with differential backups
- **One-Click Restore**: Fast server restoration from backups
- **Backup Management**: Retention policies, compression, and encryption

### 👥 User Management
- **Role-Based Access**: Super Admin, Admin, Reseller, User, and Sub-user roles
- **Two-Factor Authentication**: Enhanced security with 2FA support
- **OAuth Integration**: Login with Google, Discord, and Steam
- **Sub-Accounts**: Reseller and user hierarchy management
- **Permission System**: Granular access control for all features

### 💳 Billing & Subscriptions
- **Payment Integration**: Stripe, PayPal, and custom gateway support
- **Flexible Plans**: Usage-based billing with resource limits
- **Subscription Management**: Automated billing cycles and renewals
- **Invoice System**: Automated invoice generation and delivery
- **Resource Monitoring**: Real-time usage tracking against plan limits

### 🖥️ Node Management
- **Multi-Node Architecture**: Distribute servers across multiple nodes
- **Load Balancing**: Automatic server placement and resource optimization
- **Node Monitoring**: Real-time node health and resource tracking
- **Geographic Distribution**: Global server deployment capabilities
- **Maintenance Mode**: Graceful node maintenance and migration

### 📊 Analytics & Monitoring
- **Performance Metrics**: Detailed server and user analytics
- **Resource Usage**: Historical data and trending analysis
- **Player Statistics**: Game-specific metrics and player tracking
- **Alerts System**: Automated notifications for issues and events
- **Custom Dashboards**: Personalized monitoring interfaces

### 🔌 API & Integrations
- **RESTful API**: Complete API coverage for all panel functions
- **WebSocket Support**: Real-time updates and live monitoring
- **Webhook System**: External service integrations
- **Discord Bot**: Server management through Discord
- **WHMCS Integration**: Billing system synchronization

## 🛠️ Technology Stack

### Backend
- **PHP 8.1+** with Laravel 10.x
- **MySQL/PostgreSQL** for primary data storage
- **Redis** for caching and session management
- **JWT Authentication** with refresh token rotation
- **Queue System** for background job processing

### Frontend
- **Blade Templates** with modern CSS framework
- **Tailwind CSS** for responsive design
- **Alpine.js** for interactive components
- **Chart.js** for data visualization
- **WebSocket** for real-time updates

### Infrastructure
- **Docker** containerization for easy deployment
- **Nginx** web server with optimized configuration
- **Supervisor** for process management
- **AWS S3** integration for file storage
- **CI/CD** ready with GitHub Actions

## 📦 Installation

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/playpulse-panel.git](https://github.com/hexlorddev/playpulse-panel.git 
   cd playpulse-panel
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database and application settings
   ```

3. **Start with Docker Compose**
   ```bash
   docker-compose up -d
   ```

4. **Run initial setup**
   ```bash
   docker exec -it playpulse-panel php artisan key:generate
   docker exec -it playpulse-panel php artisan migrate --seed
   ```

5. **Access the panel**
   - Open `http://localhost` in your browser
   - Login with the default admin credentials

### Manual Installation

1. **Requirements**
   - PHP 8.1 or higher
   - Composer
   - Node.js 16+ and NPM
   - MySQL 8.0+ or PostgreSQL 13+
   - Redis 6.0+

2. **Install dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configure application**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start services**
   ```bash
   php artisan serve
   php artisan queue:work
   ```

## 🔧 Configuration

### Environment Variables

```env
# Application
APP_NAME="PlayPulse"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=playpulse
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# Payment Gateways
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_secret

# File Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

### Server Templates

Create custom server templates in `database/seeders/ServerTemplateSeeder.php`:

```php
ServerTemplate::create([
    'name' => 'Minecraft Paper 1.20.1',
    'slug' => 'minecraft-paper-1-20-1',
    'category' => 'minecraft',
    'game' => 'minecraft',
    'docker_image' => 'playpulse/minecraft:paper-1.20.1',
    'startup_command' => 'java -Xms{{MEMORY}}M -Xmx{{MEMORY}}M -jar server.jar',
    'default_port' => 25565,
    'min_memory' => 1024,
    'max_memory' => 8192,
    // ... other configuration
]);
```

## 📚 API Documentation

### Authentication

All API requests require authentication using Bearer tokens:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     https://your-panel.com/api/v1/servers
```

### Server Management

```bash
# List servers
GET /api/v1/servers

# Create server
POST /api/v1/servers
{
    "name": "My Server",
    "template_id": 1,
    "memory": 2048,
    "cpu": 100,
    "disk": 5120
}

# Server controls
POST /api/v1/servers/{id}/start
POST /api/v1/servers/{id}/stop
POST /api/v1/servers/{id}/restart

# Get server info
GET /api/v1/servers/{id}
```

### File Management

```bash
# List files
GET /api/v1/servers/{id}/files?path=/

# Upload file
POST /api/v1/servers/{id}/files/upload

# Download file
GET /api/v1/servers/{id}/files/download?file=server.properties
```

## 🎯 Game-Specific Features

### Minecraft
- **Version Management**: Support for all major Minecraft versions
- **Plugin Management**: Automatic plugin installation and updates
- **World Management**: Multiple world support and generation
- **Player Management**: Whitelist, ban, and operator controls
- **Performance Optimization**: Automatic JVM tuning and optimization

### Source Games
- **Map Management**: Automatic map downloads and rotation
- **Mod Support**: Workshop integration and mod management
- **RCON Integration**: Remote console access and control
- **Statistics Tracking**: Player stats and server metrics

## 🔒 Security Features

- **Container Isolation**: Each server runs in its own Docker container
- **Resource Limits**: Strict CPU, memory, and disk quotas
- **Network Security**: Firewall rules and port management
- **File System Protection**: Chroot jails and permission controls
- **DDoS Protection**: Rate limiting and traffic filtering
- **Audit Logging**: Complete activity logging and monitoring

## 🚀 Performance & Scaling

- **Horizontal Scaling**: Add nodes to increase capacity
- **Load Balancing**: Automatic server placement optimization
- **Caching**: Redis-based caching for improved performance
- **Queue Processing**: Background job processing for heavy tasks
- **CDN Integration**: Static asset delivery optimization
- **Database Optimization**: Query optimization and indexing

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 License

PlayPulse is open-source software licensed under the [MIT License](LICENSE).

## 🆘 Support

- **Documentation**: [docs.playpulse.com](https://docs.playpulse.com)
- **Discord**: [Join our community](https://discord.gg/playpulse)
- **Issues**: [GitHub Issues](https://github.com/your-org/playpulse-panel/issues)
- **Email**: support@playpulse.com

## 🙏 Acknowledgments

- Laravel framework and community
- Docker and containerization ecosystem
- All the game server communities that inspired this project

---

**PlayPulse** - Empowering the next generation of game server hosting.
