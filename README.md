# Xilancer Freelancer Marketplace - Docker Deployment

This repository contains the Docker deployment configuration for the Xilancer freelancer marketplace application.

## Prerequisites

- Docker and Docker Compose installed on your system
- At least 4GB of available RAM
- Port 9090 available on your host machine

## Quick Start

1. **Clone or extract the project files to this directory**
2. **Start the application:**
   ```bash
   docker-compose up -d
   ```

3. **Access the application:**
   - Main Application: http://localhost:9090
   - phpMyAdmin: http://localhost:8081
   - MySQL: localhost:3307

## Services

### Application (Port 9090/9443)
- **Container**: `xilancer_web`
- **Technology**: PHP 8.1 + Apache
- **URL**: http://localhost:9090
  - HTTPS (self-signed, for local): https://localhost:9443

### Database (Port 3307)
- **Container**: `xilancer_mysql`
- **Technology**: MySQL 8.0
- **Database**: `xilancer_db`
- **Username**: `xilancer_user`
- **Password**: `xilancer_pass123`
- **Root Password**: `rootpassword123`

### phpMyAdmin (Port 8081)
- **Container**: `xilancer_phpmyadmin`
- **URL**: http://localhost:8081
- **Username**: `root`
- **Password**: `rootpassword123`

## Installation Process

The application follows the standard Xilancer installation process as described in the [official documentation](https://docs.xgenious.com/docs/xilancer-freelancer-marketplace/instruction/installation-process/).

### First Time Setup

1. Start the containers:
   ```bash
   docker-compose up -d
   ```

2. Wait for the containers to be ready (check logs):
   ```bash
   docker-compose logs -f web | cat
   ```

3. Access the application at http://localhost:9090

4. Follow the installation wizard (as per the official guide):
   - Database Host: `mysql`
   - Database Name: `xilancer_db`
   - Database Username: `xilancer_user`
   - Database Password: `xilancer_pass123`
   - Port: `3306`
   - Driver: `mysql`

5. If prompted for file/folder permissions in the wizard, ensure they are set to `0755`. The container already enforces this on `/var/www/html` and write access for `core/storage` and `core/bootstrap/cache`.

6. If your host is Hostinger, and the installer page does not load, see the vendor note about `.htaccess` recreation. Our Apache is already configured with `AllowOverride All` inside the container.

## Management Commands

### Start the application
```bash
docker-compose up -d
```

### Stop the application
```bash
docker-compose down
```

### View logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f web
docker-compose logs -f mysql
```

### Restart services
```bash
docker-compose restart
```

### Access application container
```bash
docker-compose exec web bash
```

### Access MySQL container
```bash
docker-compose exec mysql mysql -u root -p
```

## File Structure

```
.
├── docker-compose.yml          # Main Docker Compose configuration
├── Dockerfile                  # PHP/Apache container configuration
├── docker-entrypoint.sh       # Application startup script
├── index.php                   # Application entry point (loads `core/`)
└── core/                       # Laravel application
```

## Troubleshooting

### Port Conflicts
If port 9090 is already in use, modify the `docker-compose.yml` file:
```yaml
ports:
  - "YOUR_PORT:80"  # Change YOUR_PORT to an available port
```

### Permission Issues
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
```

### Database Connection Issues
```bash
# Check MySQL status
docker-compose exec mysql mysqladmin -u root -p ping

# Restart MySQL
docker-compose restart mysql
```

### Clear Application Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

## Production Considerations

1. **Change default passwords** in `docker-compose.yml`
2. **Set `APP_DEBUG=false`** in the application's `.env` file
3. **Configure proper backup** for MySQL data volume
4. **Set up SSL/HTTPS** using a reverse proxy like Nginx
5. **Monitor logs** and set up log rotation

## Support

- For Xilancer-specific issues, refer to the [official documentation](https://docs.xgenious.com/docs/xilancer-freelancer-marketplace/)
- For Docker deployment issues, check the container logs using `docker-compose logs`

