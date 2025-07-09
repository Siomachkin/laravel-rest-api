# Laravel REST API

> User management REST API with CRUD operations, multiple email addresses support, and asynchronous welcome email sending.

## Features
- Complete user management (Create, Read, Update, Delete)
- Multiple email addresses per user with primary designation  
- Asynchronous welcome email notifications
- Interactive API documentation powered by Scribe

## Requirements

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

##  Installation

### 1. Clone the repository
```bash
git clone https://github.com/Siomachkin/laravel-rest-api.git
cd laravel-rest-api
```

### 2. Copy the environment file
```bash
cp .env.dev .env
```

### 3. Build Docker containers
```bash
docker-compose build --no-cache
```

### 4. Start the containers
```bash
docker-compose up -d
```

### 5. Install PHP dependencies
```bash
docker-compose exec app composer install
```

### 6. Generate the application key
```bash
docker-compose exec app php artisan key:generate
```

### 7. Run database migrations
```bash
docker-compose exec app php artisan migrate
```

## Usage

### Accessing the Application
- **Main Application:** [http://localhost:8080](http://localhost:8080)  
- **API Documentation:** [http://localhost:8080/docs](http://localhost:8080/docs)  
- **Mailpit (Email Testing):** [http://localhost:8025](http://localhost:8025)

## Running the Application

### Run in foreground
```bash
docker-compose up
```

### Run in background
```bash
docker-compose up -d
```

## Database Seeding

### Run all seeders
```bash
docker-compose exec app php artisan db:seed
```

## Testing

### Run all tests
```bash
docker-compose exec app php artisan test
```

