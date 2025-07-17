# Laravel REST API

> Professional-grade User Management REST API with enterprise-level code quality, security, and performance optimizations.

## Features

### Core Functionality
- **Complete User Management**: Full CRUD operations with comprehensive validation
- **Multi-Email Support**: Users can have multiple email addresses with primary designation
- **Asynchronous Processing**: Welcome emails sent via Redis queues for scalability
- **Search & Pagination**: Efficient search across user data with optimized pagination

### Code Quality & Security
- **Service Layer Architecture**: Clean separation of concerns with dedicated service classes
- **Input Sanitization**: XSS protection and comprehensive input validation
- **Exception Handling**: Structured error responses with proper HTTP status codes
- **Rate Limiting**: Configurable API rate limits to prevent abuse
- **Database Optimization**: Strategic indexes and efficient query patterns

### Developer Experience
- **Interactive API Documentation**: Powered by Scribe with live testing interface
- **Type Safety**: Comprehensive request validation with custom form requests
- **Logging & Monitoring**: Structured logging for debugging and monitoring
- **Consistent Responses**: Standardized API response format across all endpoints

## Architecture

### Clean Architecture Implementation
```
├── Controllers/          # HTTP request handling
├── Services/            # Business logic layer
├── Requests/            # Input validation & sanitization
├── Resources/           # API response formatting
├── Models/              # Database entities
├── Traits/              # Reusable functionality
└── Exceptions/          # Custom exception handling
```

### Key Design Patterns
- **Service Layer Pattern**: Business logic separated from controllers
- **Repository Pattern**: Data access abstraction (via Eloquent)
- **Request/Response Pattern**: Consistent API formatting
- **Exception Handling**: Centralized error management

## Requirements

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## Installation

### 1. Clone and Setup
```bash
git clone https://github.com/Siomachkin/laravel-rest-api.git
cd laravel-rest-api
cp .env.dev .env
```

### 2. Build and Start Services
```bash
docker-compose build --no-cache
docker-compose up -d
```

### 3. Install Dependencies and Configure
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

### 4. Optional: Seed Database
```bash
docker-compose exec app php artisan db:seed
```

## Usage

### Service URLs
- **API Base URL:** [http://localhost:8080/api/v1](http://localhost:8080/api/v1)
- **API Documentation:** [http://localhost:8080/docs](http://localhost:8080/docs)
- **Email Testing (Mailpit):** [http://localhost:8025](http://localhost:8025)

### API Endpoints

#### User Management
- `GET /api/v1/users` - List users (with search & pagination)
- `POST /api/v1/users` - Create new user
- `GET /api/v1/users/{id}` - Get user details
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Delete user
- `POST /api/v1/users/{id}/send-welcome` - Send welcome email

#### Email Management
- `GET /api/v1/users/{id}/emails` - List user emails
- `POST /api/v1/users/{id}/emails` - Add email to user
- `PUT /api/v1/users/{id}/emails/{email_id}` - Update email
- `DELETE /api/v1/users/{id}/emails/{email_id}` - Delete email
- `PATCH /api/v1/users/{id}/emails/{email_id}/set-primary` - Set primary email

## Development

### Code Quality Commands
```bash
# Run tests
docker-compose exec app php artisan test

# Code formatting
docker-compose exec app ./vendor/bin/pint

# Type checking (if PHPStan is installed)
docker-compose exec app ./vendor/bin/phpstan

# Generate API documentation
docker-compose exec app php artisan scribe:generate
```

### Development Workflow
```bash
# Start development environment
docker-compose up -d

# Watch logs
docker-compose logs -f app

# Stop services
docker-compose down
```

## Performance & Scaling

### Database Optimizations
- Strategic indexes on frequently queried fields
- Efficient relationship loading with eager loading
- Optimized pagination queries

### Queue Processing
- Redis-based queue system for email processing
- Horizontal scaling with multiple queue workers
- Delayed job processing for rate limiting

### Caching Strategy
- Redis caching for session management
- Optimized query patterns to reduce database load

## Security Features

### Input Validation
- Comprehensive request validation
- XSS protection through input sanitization
- SQL injection prevention via Eloquent ORM

### API Security
- Rate limiting to prevent abuse
- Structured error responses (no sensitive data exposure)
- Input sanitization for all user inputs

## Monitoring & Logging

### Logging Strategy
- Structured logging for all business operations
- Error tracking and debugging information
- Performance monitoring capabilities

### Health Monitoring
- Database connection monitoring
- Queue system health checks
- Redis connectivity verification

## Contributing

### Code Standards
- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting
- Maintain comprehensive test coverage
- Document all public methods

### Pull Request Process
1. Create feature branch from `main`
2. Implement changes with tests
3. Run code quality checks
4. Update documentation if needed
5. Submit PR with detailed description

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

