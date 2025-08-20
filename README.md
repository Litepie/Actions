# 🚀 Litepie Actions - Advanced Laravel Action Pattern Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/litepie/actions.svg?style=flat-square)](https://packagist.org/packages/litepie/actions)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/litepie/actions/run-tests?label=tests)](https://github.com/litepie/actions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/litepie/actions/Check%20&%20fix%20styling?label=code%20style)](https://github.com/litepie/actions/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/litepie/actions.svg?style=flat-square)](https://packagist.org/packages/litepie/actions)

A comprehensive Laravel package for implementing the Action pattern with advanced features including authorization, validation, caching, sub-action orchestration, form management, event handling, and comprehensive logging.

## ✨ Features

### 🎯 **Core Functionality**
- **Action Pattern Implementation** - Clean, testable business logic organization
- **Multiple Action Types** - BaseAction, StandardAction, CompleteAction
- **Trait-Based Architecture** - Mix and match functionality as needed
- **Result Objects** - Standardized success/failure handling

### 🔐 **Authorization & Security**
- **Gate-Based Authorization** - Laravel gate integration
- **User Context Tracking** - Full user authentication support
- **Permission Checking** - Automatic authorization validation

### ✅ **Validation & Forms**
- **Laravel Validator Integration** - Built-in validation support
- **Dynamic Form Generation** - Auto-generate forms from actions
- **Custom Validation Rules** - Flexible validation configuration
- **Form-Action Integration** - Seamless form and validation flow

### 🔄 **Sub-Actions & Orchestration**
- **Sub-Action Execution** - Execute related actions automatically
- **Conditional Logic** - Run sub-actions based on conditions
- **Before/After Hooks** - Control execution timing
- **Error Handling** - Continue or fail based on configuration

### 📧 **Notifications & Events**
- **Automatic Notifications** - Send notifications on action completion
- **Laravel Events** - Fire events during action lifecycle
- **Multiple Recipients** - Notify different user groups
- **Queue Integration** - Async notification delivery

### 📊 **Logging & Auditing**
- **Comprehensive Logging** - Full action execution tracking
- **ActionLog Model** - Database storage with relationships
- **Rich Context** - User, IP, timestamps, properties
- **Searchable History** - Query and filter action logs

### ⚡ **Performance & Scalability**
- **Result Caching** - Cache action results for performance
- **Async Execution** - Queue-based background processing
- **Batch Processing** - Execute multiple actions efficiently
- **Pipeline Support** - Chain actions with middleware
- **Retry Logic** - Automatic retry with exponential backoff

### 🎛️ **Advanced Features**
- **Action Manager** - Centralized action registration and execution
- **Middleware Support** - Rate limiting, logging, custom middleware
- **Conditional Actions** - Execute different actions based on conditions
- **Metrics Collection** - Execution time and memory tracking
- **Helper Functions** - Convenient global helper functions

## 📦 Installation

Install the package via Composer:

```bash
composer require litepie/actions
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=actions-config
```

Run the migrations to create the action logs table:

```bash
php artisan migrate
```

## 🚀 Quick Start

### Creating Your First Action

Generate a new action using the Artisan command:

```bash
php artisan make:action CreateUserAction --validation --cacheable
```

This creates a basic action class:

```php
<?php

namespace App\Actions;

use App\Models\User;
use Litepie\Actions\StandardAction;

class CreateUserAction extends StandardAction
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    protected function handle(): User
    {
        return User::create([
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => bcrypt($this->data['password']),
        ]);
    }
}
```

### Basic Usage

```php
// Execute the action
$result = CreateUserAction::execute(null, [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123'
], $currentUser);

if ($result->isSuccess()) {
    $user = $result->getData();
    echo "User created: " . $user->name;
} else {
    echo "Error: " . $result->getMessage();
}
```

### Fluent Interface

```php
$result = CreateUserAction::make(null, $userData, $currentUser)
    ->cached('user-creation', 1800)
    ->retries(3)
    ->timeout(60)
    ->executeWithRetry();
```

## 📚 Action Types

### BaseAction - Foundation Class

The minimal action class with core functionality:

```php
use Litepie\Actions\BaseAction;

class SimpleAction extends BaseAction
{
    protected function handle(): mixed
    {
        return ['message' => 'Action completed'];
    }
}
```

### StandardAction - Common Use Case

Includes authorization, validation, and logging:

```php
use Litepie\Actions\StandardAction;

class StandardUserAction extends StandardAction
{
    protected function rules(): array
    {
        return ['email' => 'required|email'];
    }

    protected function handle(): mixed
    {
        // Your business logic here
    }
}
```

### CompleteAction - Full-Featured

Includes all available traits for complex workflows:

```php
use Litepie\Actions\CompleteAction;

class ComplexOrderAction extends CompleteAction
{
    protected function rules(): array
    {
        return ['order_id' => 'required|exists:orders,id'];
    }

    protected function getSubActions(string $timing): array
    {
        return $timing === 'after' ? [
            ['action' => SendInvoiceAction::class],
            ['action' => UpdateInventoryAction::class],
        ] : [];
    }

    protected function getNotifications(): array
    {
        return [
            [
                'recipients' => User::admins()->get(),
                'class' => OrderProcessedNotification::class
            ]
        ];
    }

    protected function handle(): Order
    {
        $order = Order::find($this->data['order_id']);
        $order->update(['status' => 'processed']);
        return $order;
    }
}
```

## 🎭 Traits System

Mix and match functionality using traits:

### Available Traits

- **AuthorizesActions** - Gate-based authorization
- **ExecutesSubActions** - Sub-action orchestration
- **HandlesNotificationsAndEvents** - Notification and event management
- **LogsActions** - Comprehensive action logging
- **ManagesForms** - Dynamic form generation
- **HasValidation** - Laravel validation integration
- **HasCaching** - Result caching capabilities
- **HasEvents** - Event firing support
- **HasMetrics** - Performance metrics collection

### Custom Action with Specific Traits

```php
use Litepie\Actions\BaseAction;
use Litepie\Actions\Traits\AuthorizesActions;
use Litepie\Actions\Traits\LogsActions;

class CustomAction extends BaseAction
{
    use AuthorizesActions, LogsActions;

    protected function handle(): mixed
    {
        // Your custom logic
    }
}
```

## 🔄 Sub-Actions

Execute related actions automatically with conditional logic:

```php
class ProcessOrderAction extends CompleteAction
{
    protected function getSubActions(string $timing): array
    {
        if ($timing === 'before') {
            return [
                [
                    'action' => ValidateInventoryAction::class,
                    'data' => ['check_stock' => true],
                ],
            ];
        }

        if ($timing === 'after') {
            return [
                [
                    'action' => SendInvoiceAction::class,
                    'condition' => fn($data) => $data['send_invoice'] ?? false,
                ],
                [
                    'action' => UpdateInventoryAction::class,
                    'continue_on_failure' => true,
                ],
                [
                    'action' => NotifyCustomerAction::class,
                    'data' => ['template' => 'order_confirmation'],
                ],
            ];
        }

        return [];
    }
}
```

## 📝 Form Management

Generate dynamic forms from your actions:

```php
class CreateUserAction extends CompleteAction
{
    protected function getFormFields(): array
    {
        return [
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Full Name',
                'required' => true,
            ],
            [
                'name' => 'email',
                'type' => 'email',
                'label' => 'Email Address',
                'required' => true,
            ],
            [
                'name' => 'role',
                'type' => 'select',
                'label' => 'User Role',
                'options' => [
                    'user' => 'Regular User',
                    'admin' => 'Administrator',
                ],
            ],
        ];
    }

    protected function getFormValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}

// Check if action requires a form
$action = new CreateUserAction();
if ($action->requiresForm()) {
    $formConfig = $action->renderForm();
    // Use $formConfig to render your form
}
```

## 📊 Action Manager

Centrally manage and execute actions:

```php
use Litepie\Actions\Facades\ActionManager;

// Register actions
ActionManager::register('create-user', CreateUserAction::class);
ActionManager::register('process-order', ProcessOrderAction::class);

// Execute registered actions
$result = ActionManager::execute('create-user', $userData);

// Chain multiple actions
$results = ActionManager::chain([
    ['name' => 'create-user', 'data' => $userData],
    ['name' => 'send-welcome-email', 'use_previous_result' => true],
]);

// Execute actions in parallel
$results = ActionManager::parallel([
    ['name' => 'create-user', 'data' => $user1Data],
    ['name' => 'create-user', 'data' => $user2Data],
]);

// Get execution statistics
$stats = ActionManager::getStatistics();
```

## 🔄 Batch Processing

Execute multiple actions efficiently:

```php
use Litepie\Actions\Batch\ActionBatch;

$batch = ActionBatch::make()
    ->add(new CreateUserAction(null, $user1Data))
    ->add(new CreateUserAction(null, $user2Data))
    ->add(new CreateUserAction(null, $user3Data))
    ->concurrent(3)
    ->stopOnFailure(false)
    ->execute();

$successful = $batch->getSuccessful();
$failed = $batch->getFailed();
$allSuccessful = $batch->isAllSuccessful();
```

## 🔗 Pipeline Processing

Chain actions with middleware:

```php
use Litepie\Actions\Pipeline\ActionPipeline;

$result = ActionPipeline::make()
    ->send($initialData)
    ->through([
        ValidateDataAction::class,
        ProcessDataAction::class,
        SaveDataAction::class,
    ])
    ->via([
        LoggingMiddleware::class,
        RateLimitMiddleware::class,
    ])
    ->execute();
```

## 🎯 Conditional Actions

Execute different actions based on conditions:

```php
use Litepie\Actions\Conditional\ConditionalAction;

$result = ConditionalAction::make()
    ->when(fn($data) => $data['user_type'] === 'premium')
    ->then(CreatePremiumUserAction::class)
    ->otherwise(CreateRegularUserAction::class)
    ->with($userData)
    ->execute();

// Or using helper function
$result = action_when(fn($data) => $data['amount'] > 1000)
    ->then(ProcessLargeOrderAction::class)
    ->otherwise(ProcessRegularOrderAction::class)
    ->with($orderData)
    ->execute();
```

## 🛡️ Middleware

Add middleware for cross-cutting concerns:

```php
use Litepie\Actions\Middleware\LoggingMiddleware;
use Litepie\Actions\Middleware\RateLimitMiddleware;

// Built-in middleware
class MyAction extends BaseAction
{
    // Middleware will be applied automatically in pipelines
}

// Custom middleware
class CustomMiddleware extends ActionMiddleware
{
    public function handle(ActionContract $action, Closure $next): mixed
    {
        // Before action execution
        $result = $next($action);
        // After action execution
        return $result;
    }
}
```

## 📈 Caching

Cache action results for improved performance:

```php
// Enable caching for specific executions
$result = CreateUserAction::make($model, $data)
    ->cached('user-creation-' . $data['email'], 3600)
    ->execute();

// Cache with custom key and TTL
$result = ProcessReportAction::make()
    ->with($reportData)
    ->cached('report-' . $reportId, 7200)
    ->execute();

// Clear cached results
$action->clearCache();

// Disable caching for this execution
$result = $action->fresh()->execute();
```

## 🔁 Retry Logic

Handle failures with automatic retry:

```php
// Configure retries
$result = UnstableApiAction::make()
    ->with($apiData)
    ->retries(5)
    ->timeout(30)
    ->executeWithRetry();

// Exponential backoff is applied automatically
// Retry delays: 1s, 2s, 4s, 8s, 16s
```

## ⚡ Async Execution

Execute actions in the background:

```php
// Execute asynchronously
CreateUserAction::make($model, $data, $user)->executeAsync();

// Or use Laravel's dispatch
CreateUserAction::dispatch($model, $data, $user);

// Delayed execution
CreateUserAction::executeLater(now()->addMinutes(30), $model, $data, $user);
```

## 📊 Logging & Auditing

Track all action executions:

```php
use Litepie\Actions\Models\ActionLog;

// Query action logs
$logs = ActionLog::inLog('actions')
    ->where('action', 'CreateUser')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();

// Get logs for specific model
$userLogs = ActionLog::forSubject($user)->get();

// Get logs by user
$adminLogs = ActionLog::causedBy($admin)->get();

// Get log properties
$log = ActionLog::first();
$executionTime = $log->getExtraProperty('execution_time');
$userAgent = $log->getExtraProperty('user_agent');
```

## 🔧 Configuration

Customize the package behavior:

```php
// config/actions.php
return [
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'action',
    ],
    
    'logging' => [
        'enabled' => true,
        'default_log_name' => 'actions',
        'delete_records_older_than_days' => 365,
    ],
    
    'authorization' => [
        'enabled' => true,
        'require_authenticated_user' => true,
    ],
    
    'sub_actions' => [
        'enabled' => true,
        'max_depth' => 5,
        'continue_on_failure' => false,
    ],
    
    // ... more configuration options
];
```

## 🎨 Helper Functions

Convenient global functions:

```php
// Execute registered action
$result = action('create-user', $userData);

// Create action batch
$batch = action_batch()
    ->add($action1)
    ->add($action2)
    ->execute();

// Create action pipeline
$result = action_pipeline()
    ->send($data)
    ->through($actions)
    ->execute();

// Conditional action execution
$result = action_when($condition)
    ->then(ActionA::class)
    ->otherwise(ActionB::class)
    ->execute();
```

## 🧪 Testing

The package includes comprehensive tests and testing utilities:

```php
// Test your actions
class CreateUserActionTest extends TestCase
{
    /** @test */
    public function it_creates_a_user_successfully()
    {
        $userData = ['name' => 'John', 'email' => 'john@example.com'];
        $result = CreateUserAction::execute(null, $userData, $this->user);
        
        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(User::class, $result->getData());
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(ValidationException::class);
        CreateUserAction::execute(null, [], $this->user);
    }
}
```

## 📖 Advanced Examples

### Complex E-commerce Order Processing

```php
class ProcessOrderAction extends CompleteAction
{
    protected function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:card,bank,paypal',
        ];
    }

    protected function getSubActions(string $timing): array
    {
        if ($timing === 'before') {
            return [
                [
                    'action' => ValidateInventoryAction::class,
                    'data' => ['strict_check' => true],
                ],
                [
                    'action' => CalculateShippingAction::class,
                    'condition' => fn($data) => $data['shipping_required'] ?? true,
                ],
            ];
        }

        if ($timing === 'after') {
            return [
                [
                    'action' => ProcessPaymentAction::class,
                    'data' => ['method' => $this->data['payment_method']],
                ],
                [
                    'action' => UpdateInventoryAction::class,
                    'continue_on_failure' => false,
                ],
                [
                    'action' => SendOrderConfirmationAction::class,
                    'continue_on_failure' => true,
                ],
                [
                    'action' => CreateShippingLabelAction::class,
                    'condition' => fn($data) => $data['shipping_required'] ?? true,
                    'continue_on_failure' => true,
                ],
            ];
        }

        return [];
    }

    protected function getNotifications(): array
    {
        return [
            [
                'recipients' => [$this->model->customer],
                'class' => OrderProcessedNotification::class,
            ],
            [
                'recipients' => User::role('warehouse')->get(),
                'class' => NewOrderNotification::class,
            ],
        ];
    }

    protected function getEvents(): array
    {
        return [
            OrderProcessedEvent::class,
            InventoryUpdatedEvent::class,
        ];
    }

    protected function handle(): Order
    {
        $order = Order::find($this->data['order_id']);
        
        $order->update([
            'status' => 'processing',
            'processed_at' => now(),
            'processed_by' => $this->user->id,
        ]);

        return $order;
    }

    protected function getDescription(string $status): string
    {
        $orderId = $this->data['order_id'];
        return "Order #{$orderId} processing " . ($status === 'success' ? 'completed' : 'failed');
    }
}
```

### Batch User Import with Error Handling

```php
$users = collect($csvData);
$batch = ActionBatch::make()->concurrent(10);

foreach ($users->chunk(100) as $userChunk) {
    foreach ($userChunk as $userData) {
        $batch->add(new CreateUserAction(null, $userData, $admin));
    }
}

$results = $batch->execute();

// Handle results
$successful = $batch->getSuccessful();
$failed = $batch->getFailed();

Log::info("Batch import completed", [
    'total' => $results->count(),
    'successful' => $successful->count(),
    'failed' => $failed->count(),
]);
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- **[Litepie Team](https://github.com/litepie)**
- **[All Contributors](https://github.com/litepie/actions/contributors)**

## 📞 Support

- **Documentation**: [Full Documentation](https://litepie.github.io/actions)
- **Issues**: [GitHub Issues](https://github.com/litepie/actions/issues)
- **Discussions**: [GitHub Discussions](https://github.com/litepie/actions/discussions)
- **Email**: support@litepie.com

---

<p align="center">
  <strong>Built with ❤️ by the Litepie Team</strong>
</p>