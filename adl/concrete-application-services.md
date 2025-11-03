## Concrete Application Services Without Interfaces

### Context

DDD typically recommends defining application services behind interfaces for flexibility, testability,
and adherence to the Dependency Inversion Principle.
This allows swapping implementations and easier mocking in tests.

### Decision

Implement application services as concrete classes without interface abstractions.

```php
// Concrete service - no interface
final readonly class InvoiceCreator {}
```

### Alternatives Considered

**Application Service Interfaces**

- ✅ Dependency Inversion Principle - depend on abstractions
- ✅ Easy to swap implementations at runtime
- ✅ Simpler test mocking with interface stubs
- ❌ Additional abstraction layer for single-implementation services
- ❌ More code ceremony in small/simple projects
- ❌ Excessive interfaces when unlikely to have multiple implementations

### Rationale

1. **Single Implementation**: Application services like `InvoiceCreator` are highly unlikely to have alternative
   implementations.
2. **Project Scope**: Small, focused project where flexibility of swapping service implementations provides minimal
   value.
3. **Reduced Ceremony**: Fewer files to maintain, simpler navigation, less boilerplate.
4. **Testability Preserved**: Services can still be tested - mock the repository interface they depend on, not the
   service itself.

### Trade-offs

**Accepted**: Controllers and other consumers directly depend on concrete service classes, reducing abstraction
flexibility.

**Mitigation**:

- Repository layer remains abstracted via interfaces (persistence flexibility preserved)
- If multiple implementations become necessary, interfaces can be introduced later
  (refactoring effort is acceptable for this unlikely scenario)

### References

- Concrete services: `src/Modules/Invoices/Application/Services/InvoiceCreator.php`
- Usage of concrete services: `src/Modules/Invoices/Application/Controllers/InvoiceController.php`
