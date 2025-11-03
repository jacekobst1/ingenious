## Exception-Driven Control Flow for Business Rules

### Context

Domain entities must enforce business invariants (e.g., "Invoice can only be sent from Draft status") and communicate
violations to the HTTP layer.

### Decision

Domain entities throw typed exceptions for business rule violations, transformed to HTTP responses at the
framework-infrastructure layer.

```php
// Domain: Throw exceptions
if ($this->status !== StatusEnum::Draft) {
    throw SendInvoiceException::mustBeDraft($this->id, $this->status);
}

// Shared Infrastructure: Transform to HTTP
$exceptions->render(function (MyDomainException $e) {
    return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
});
```

### Alternatives Considered

**Result Objects Pattern**: Return `Result::success()` or `Result::failure($error)` instead of throwing

- ✅ Pure DDD approach
- ✅ No infrastructure coupling
- ❌ More boilerplate
- ❌ Requires translating each failure to a proper http exception in the Application layer

**Boolean Returns**: Return `true/false` with error properties

- ✅ Simple
- ❌ Poor type safety
- ❌ Unclear errors

### Rationale

1. **Clear Intent**: Named constructors (`SendInvoiceException::mustBeDraft()`) are self-documenting.
2. **Fail-Fast**: Automatic flow control prevents invalid state propagation.
3. **Developer Experience**: Clean happy path code, centralized error handling, excellent testability.
4. **Type Safety**: Each exception type represents specific business rule violation.

### Trade-offs

**Accepted**: Domain exceptions include `getStatusCode()` method, introducing HTTP awareness into domain layer.

**Mitigation**: If domain needs non-HTTP reuse (CLI, queues), the `getStatusCode()` method can be ignored or we can
introduce exception mapping layer.

### References

- Implementation: `src/Modules/Invoices/Domain/Entities/Invoice.php`
- Exceptions: `src/Modules/Invoices/Domain/Exceptions/`
- HTTP mapping: `bootstrap/app.php`
