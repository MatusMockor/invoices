Senior Laravel Developer (20+ years experience) | Expert in Laravel, PHP, PHPUnit, React, Tailwind CSS

---

# Laravel Coding Standards (v2.0 – Unified & AI-friendly)

## 1. Coding & Language Standards

* PHP **v8.4**
* Laravel **v12**
* Use **strict types** and **typed properties**
* Follow **SOLID principles**
* Run `./vendor/bin/pint` after modifying files
* Each Repository and Service has an **interface** (without `Interface` suffix)
* Interface aliases in `AppServiceProvider` end with `Contract`
* Register bindings:

  ```php
  $this->app->bind(UserRepositoryContract::class, EloquentUserRepository::class);
  ```
* **Avoid `else` statements**; use guard clauses
* For null checks, use `if (!$var)`
* Add comments only for exceptions and specific business logic

## 2. Architecture & Project Structure

### 2.1 Directory Structure

```
app/
├── Actions/
│   ├── Order/
│   │   ├── OrderCreateAction.php
│   │   ├── OrderItemCancelAction.php
│   └── Search/
│       ├── SearchQueryTrackLogAction.php
├── Services/
│   ├── Order/
│   │   ├── OrderConditionService.php
│   └── Supplier/
│       ├── DeliveryScheduleService.php
├── Repositories/
│   ├── UserRepository.php
│   ├── Contracts/
│   │   ├── UserRepositoryContract.php
├── Models/
│   ├── Order.php
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Resources/
```

### 2.2 General Rules

* No `.gitkeep` after adding real files
* Avoid `DB::`; always use model or repository methods
* Controllers are **thin** – only gather inputs and call Actions
* Use **FormRequest** classes for validation (`Create`, `Update`, `Delete`)
* Each model has `fillable` and registers observers via:

  ```php
  #[ObservedBy([OrderObserver::class])]
  ```
* Use **DTOs** to transfer data between layers

## 3. Actions & Services (Business Logic Layer)

### 3.1 Actions

* Located in `app/Actions/{Domain}`
* Naming: `[Domain][Object][Verb]Action` (e.g., `OrderCreateAction`)
* Each Action handles **one business operation**
* Constructor-inject dependencies (repositories, services, sub-actions)
* Main method: `handle()`
* If modifying data → wrap in `DB::transaction()`
* Exceptions bubble up to global handler
* Limit dependencies to 8; split Action if exceeding
* Controller should only:

    * Validate (FormRequest)
    * Create DTO
    * Call `Action->handle()`
    * Return Resource

**Example:**

```php
final class OrderCreateAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderConditionService $conditions,
        private readonly OrderIssueNotificationAction $notify
    ) {}

    public function handle(OrderCreateDTO $dto, int $userId): Order
    {
        $this->conditions->validate($dto);

        return DB::transaction(function () use ($dto, $userId) {
            $order = $this->orders->create($dto, $userId);
            $this->notify->handle($order);

            return $order;
        });
    }
}
```

### 3.2 Services

* Located in `app/Services/{Domain}`
* Naming: `[Domain][Purpose]Service` (e.g., `CartItemValidator`)
* **Stateless**, no side effects
* Handles calculations, validations, API calls, formatting
* Limit dependencies to 5; refactor if exceeding
* Must be reusable and unit-testable

**Example:**

```php
final class DeliveryScheduleService
{
    public function calculate(DateTime $orderDate, array $rules): DateTime
    {
        return $this->applyRules($orderDate, $rules);
    }

    private function applyRules(DateTime $orderDate, array $rules): DateTime
    {
        return $orderDate;
    }
}
```

## 4. Testing

* Run tests with Sail:

  ```bash
  ./vendor/bin/sail test
  ```
* Each model has a Factory
* Use `fake()` instead of hardcoded values
* Use `assertDatabaseHas()` with model class:
* In tests, always use the `route()` helper to generate route URLs.
* The test name must clearly describe what the test is trying to verify.
* Don’t use hardcoded values in tests — always use the `Faker` library or the `fake()` helper to generate realistic random data.

  ```php
  $this->assertDatabaseHas(Order::class, ['status' => 'pending']);
  ```
* Test directory:

```
tests/
├── Feature/
│   ├── Http/
│   └── Console/
├── Unit/
│   ├── Actions/
│   ├── Services/
│   ├── Models/
│   ├── Jobs/
```

## 5. Styling & Frontend

* Use **Tailwind CSS**
* Minimalist, responsive UI
* Recompile assets after frontend changes:

  ```bash
  npm run build
  ```

## 6. Task Completion Checklist ✅

* Pint ran successfully
* Tests passed (`sail test`)
* Actions & Services follow naming & structure standards
* Repositories, Services, and Actions are tested
* DTOs and FormRequests are used
* No `else`, no `DB::`, no magic numbers
* Assets recompiled
