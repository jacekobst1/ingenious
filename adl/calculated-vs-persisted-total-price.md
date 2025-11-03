## Calculated vs Persisted Total Price

### Context

Invoice total price can be either calculated dynamically from product lines or stored as a persisted database column.

### Decision

Calculate total price dynamically via `calculateTotalPrice()` method without database persistence.

```php
public function calculateTotalPrice(): Money
{
    $total = Money::zero(CurrencyEnum::Pln->value);

    foreach ($this->productLines as $line) {
        $total = $total->plus($line->calculateTotalPrice(), RoundingMode::HALF_EVEN);
    }

    return $total;
}
```

**Database schema**: No `total_price` column in `invoices` table.

### Alternatives Considered

**Persisted Total Price Column**

- ✅ Eliminates need to load product lines for total calculation
- ✅ Efficient for displaying totals in invoice lists (no N+1 queries)
- ✅ Enables database-level filtering/sorting by total
- ❌ Data redundancy; source of truth duplicated
- ❌ Risk of inconsistency if product lines change without recalculating
- ❌ Additional complexity: triggers, observers, or manual sync logic

### Rationale

1. **Current Use Case**: Total price is only used during validation (`markAsSending()`) where product lines are already
   loaded.
2. **Simplicity**: A single source of truth - product lines are the authoritative data.
3. **No Data Inconsistency Risk**: Calculated values can't become stale or out of sync.

### Trade-offs

**Accepted**: Lack of performance penalty with current use cases.

If invoice totals need to be displayed in large list views or used for filtering/sorting,
migrate to the alternative solution with:

- Database column
- Eloquent observer to recalculate on product line changes
- Database index for efficient querying

### References

- Calculation method: `src/Modules/Invoices/Domain/Entities/Invoice.php`
- Current usage: `src/Modules/Invoices/Domain/Entities/Invoice.php`
- Database schema: `database/migrations/2023_02_02_173650_create_invoices_table.php`
