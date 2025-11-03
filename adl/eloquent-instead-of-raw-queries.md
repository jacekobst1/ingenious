## Eloquent ORM vs Raw Database Queries

### Context

In a purist DDD approach, repositories would use raw database queries via `DB` facade or query builder,
avoiding any ORM abstraction.
This maximizes framework independence and explicit control over database operations.

### Decision

Use Eloquent ORM models for database persistence within repository implementations.

```php
// Repository
public function findById(UuidInterface $id): ?Invoice
{
    $model = InvoiceEloquentModel::find($id->toString());
    return $model ? $this->toDomainEntity($model) : null;
}

// Eloquent Model with automatic type casting
class InvoiceEloquentModel extends Model
{
    protected $casts = [
        'id' => UuidModelCast::class,
        'total_price' => MoneyModelCast::class,
        'status' => StatusEnum::class,
    ];
}
```

### Alternatives Considered

**DB Facade / Query Builder with Raw Queries**

- ✅ Zero ORM coupling - less framework independence
- ✅ Explicit control over every SQL query
- ✅ No hidden behavior or magic
- ❌ Manual type casting for every field (UUID strings → objects, cents → Money, strings → enums)
- ❌ Manual relationship loading and hydration
- ❌ Repetitive hydration code in every repository method
- ❌ No built-in attribute casting system
- ❌ More verbose for common CRUD operations

### Rationale

1. **Automatic Type Casting**: Eloquent's cast system eliminates repetitive manual conversions between database
   primitives and value objects (`UuidModelCast`, `MoneyModelCast`, enum casting).
2. **Reduced Boilerplate**: Relationship management, collection handling, and hydration happen automatically rather than
   manual mapping in every repository method.
3. **Maintainability**: Centralized casting logic in model classes versus scattered hydration code across repository
   methods.

### Trade-offs

**Accepted**: Infrastructure layer depends on Eloquent ORM, not just raw database access.

**Mitigation**:

- Repository interfaces remain ORM-agnostic contracts.
- Domain layer never touches Eloquent.
- If needed, Eloquent could be replaced in repositories without affecting application or domain layers.

### References

- Infrastructure models: `src/Modules/Invoices/Infrastructure/Models/InvoiceModel.php`
- Value object casts: `app/Casts/Model/MoneyModelCast.php`, `app/Casts/Model/UuidModelCast.php`
