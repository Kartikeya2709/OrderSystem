# Order Processing System - Technical Documentation
## Made By -Kartikeya Sharma

### Entity Relationship Diagram (ERD)
```
+---------------+       +------------------+       +---------------+
|     Items     |       |   Order_Items    |       |    Orders     |
+---------------+       +------------------+       +---------------+
| id            |       | order_id         |       | id            |
| name          |       | item_id          |       | total_amount  |
| price         |<----->| quantity         |<----->| status        |
| description   |       | price            |       | created_at    |
| stock         |       +------------------+       | updated_at    |
| created_at    |                                 +---------------+
| updated_at    |
+---------------+
```

### Data Flow Diagram (DFD)
```
[Web Interface] -----> [Order Controller]
                          |
                          v
[Database] <-----> [Transaction Manager]
                          |
                          v
[Queue] ---------> [Process Order Job] -----> [Stock Management]
                          |
                          v
                   [Status Updates]
```

### Order Processing Workflow
```
[Start Order Creation]
        |
        v
[Validate Input] -----> [Begin Transaction]
        |                      |
        v                      v
[Calculate Total] ---> [Create Order Record]
        |                      |
        v                      v
[Attach Items] -----> [Dispatch Process Job]
        |                      |
        v                      v
[Commit Transaction] <- [Return Response]
        |
        v
[Queue Processing]
        |
        v
[Process Order Job]
        |
        v
[Update Stock] ------> [Update Status]
```


###  API Endpoints

#### Orders
```
POST /orders
GET  /orders
```

#### Items
```
GET    /items
POST   /items
PUT    /items/{id}
DELETE /items/{id}
```

###  Database Transactions

#### Order Creation Transaction
1. Begin transaction
2. Create order record
3. Calculate total amount
4. Attach order items
5. Dispatch process job
6. Commit transaction

#### Order Processing Transaction
1. Begin transaction
2. Update order status to 'processing'
3. Check stock availability
4. Decrease stock levels
5. Update order status to 'completed'
6. Commit transaction

### Error Handling

#### Order Creation Errors
- Invalid input validation
- Database errors
- Transaction failures

#### Processing Errors
- Insufficient stock
- Database errors
- Transaction failures

### Monitoring

#### Laravel Telescope Features
- Database queries
- Queue jobs
- Request/Response
- Exceptions

###  Security Measures

#### Data Protection
- CSRF protection
- Input validation
- SQL injection prevention
- XSS protection

#### Transaction Safety
- Atomic operations
- Rollback on failure
- Data consistency
- Stock protection

## Installation and Deployment

### Prerequisites
```bash
- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Node.js & NPM
```

### Setup Steps
```bash
# Clone repository
git clone <repository-url>
cd order-system

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_system
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Start queue worker
php artisan queue:work

# Start development server
php artisan serve
```

### Queue Configuration
```env
QUEUE_CONNECTION=database
QUEUE_TABLE=jobs
```


-----------------------------------------------------------
