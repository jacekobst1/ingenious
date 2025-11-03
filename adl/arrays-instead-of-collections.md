## Native PHP Arrays vs Laravel Collections

### Context

Laravel Collections provide convenient methods that make working with arrays more expressive.
However, using Collections in domain and application layers creates framework coupling.

### Decision

Use native PHP arrays throughout domain and application layers.

```php
// Domain Entity
/** @param list<InvoiceProductLine> $productLines */
class Invoice
{
    private array $productLines = [];
}

// Application DTO Response
$productLines = array_map(
    static fn($line) => InvoiceProductLineResponse::fromEntity($line),
    $invoice->productLines,
);
```

### Alternatives Considered

**Laravel Collections in Domain/Application Layers**

- ✅ More expressive syntax (`$items->filter()->map()->reverse()->pluck()`)
- ✅ Rich API with 80+ convenience methods
- ✅ Familiar to Laravel developers
- ❌ Couples domain logic to Laravel framework
- ❌ Reduces portability to other frameworks or contexts
- ❌ Makes domain entities framework-dependent

**Framework-Agnostic Collection Library (e.g. ramsey/collection)**

- ✅ Framework-independent collection implementation
- ✅ Type-safe collections with generics support
- ✅ No Laravel coupling
- ❌ Very limited functionality compared to Laravel Collections
- ❌ Adds external dependency for minimal benefit over native arrays

### Rationale

1. **Framework Independence**: Domain and application layers remain portable and framework-agnostic.
2. **Domain Purity**: Business logic has no Laravel dependencies.

### Trade-offs

**Accepted**: More verbose iteration patterns (`array_map()`, `foreach`) instead of fluent Collection methods.

**Mitigation**: Use modern PHP array functions and arrow functions to maintain readability.

### References

- Domain arrays: `src/Modules/Invoices/Domain/Entities/Invoice.php`
- Array transformations: `src/Modules/Invoices/Application/Dtos/Responses/InvoiceResponse.php`
