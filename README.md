# Order Processing System - Technical Documentation
## Made By -Kartikeya Sharma
## System Architecture

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

## Technical Implementation

### 1. Models

#### Item Model
```php
class Item extends Model
{
    protected $fillable = ['name', 'price', 'description', 'stock'];
    
    public function orders()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity', 'price');
    }
}
```

#### Order Model
```php
class Order extends Model
{
    protected $fillable = ['total_amount', 'status'];
    
    public function items()
    {
        return $this->belongsToMany(Item::class)
                    ->withPivot('quantity', 'price');
    }
}
```

### 2. Controllers

#### OrderController
```php
class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Create order with items
            $order = new Order();
            $total = 0;
            
            // Calculate total and prepare items
            $orderItems = [];
            foreach ($request->items as $item) {
                $dbItem = Item::findOrFail($item['id']);
                $total += $dbItem->price * $item['quantity'];
                $orderItems[$item['id']] = [
                    'quantity' => $item['quantity'],
                    'price' => $dbItem->price
                ];
            }
            
            $order->total_amount = $total;
            $order->save();
            
            // Attach items
            $order->items()->attach($orderItems);
            
            // Dispatch job
            ProcessOrder::dispatch($order);
            
            DB::commit();
            return redirect()->back()->with('success', 'Order created');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
```

### 3. Jobs

#### ProcessOrder Job
```php
class ProcessOrder implements ShouldQueue
{
    protected $order;
    
    public function handle()
    {
        try {
            DB::beginTransaction();
            
            // Update status
            $this->order->update(['status' => 'processing']);
            
            // Process items
            foreach ($this->order->items as $item) {
                if ($item->stock < $item->pivot->quantity) {
                    throw new \Exception("Insufficient stock");
                }
                
                $item->decrement('stock', $item->pivot->quantity);
            }
            
            $this->order->update(['status' => 'completed']);
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->order->update(['status' => 'failed']);
        }
    }
}
```

### 4. API Endpoints

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

### 5. Database Transactions

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

### 6. Error Handling

#### Order Creation Errors
- Invalid input validation
- Database errors
- Transaction failures

#### Processing Errors
- Insufficient stock
- Database errors
- Transaction failures

### 7. Monitoring

#### Laravel Telescope Features
- Database queries
- Queue jobs
- Request/Response
- Exceptions
- Cache operations

### 8. Security Measures

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
