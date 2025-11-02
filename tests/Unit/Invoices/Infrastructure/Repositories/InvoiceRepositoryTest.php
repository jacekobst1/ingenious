<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Infrastructure\Repositories;

use Brick\Money\Money;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Infrastructure\Persistence\Models\InvoiceEloquentModel;
use Modules\Invoices\Infrastructure\Persistence\Repositories\InvoiceRepository;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Ramsey\Uuid\UuidInterface;

final class InvoiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceRepository $repository;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = App::make(InvoiceRepository::class);
    }

    public function testNextIdentityGeneratesUuid(): void
    {
        $uuid = $this->repository->nextIdentity();

        $this->assertInstanceOf(UuidInterface::class, $uuid);
    }

    public function testSaveAndFindInvoiceWithProductLines(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid4(),
            customerName: 'Jane Smith',
            customerEmail: 'jane@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid4(),
            name: 'Product A',
            quantity: 2,
            unitPrice: Money::ofMinor(1000, 'PLN'),
        ));

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid4(),
            name: 'Product B',
            quantity: 3,
            unitPrice: Money::ofMinor(500, 'PLN'),
        ));

        $this->repository->save($invoice);

        $found = InvoiceEloquentModel::find($invoice->id);

        $this->assertNotNull($found);
        $this->assertEquals($invoice->id, $found->id);
        $this->assertEquals('Jane Smith', $found->customer_name);
        $this->assertEquals('jane@example.com', $found->customer_email);
        $this->assertEquals(StatusEnum::Draft->value, $found->status);
        $this->assertCount(2, $found->productLines);

        $this->assertEquals('Product A', $found->productLines[0]->name);
        $this->assertEquals(2, $found->productLines[0]->quantity);
        $this->assertTrue($found->productLines[0]->price->isEqualTo(Money::ofMinor(1000, 'PLN')));

        $this->assertEquals('Product B', $found->productLines[1]->name);
        $this->assertEquals(3, $found->productLines[1]->quantity);
        $this->assertTrue($found->productLines[1]->price->isEqualTo(Money::ofMinor(500, 'PLN')));
    }

    /**
     * TEST SCENARIO
     * 1. Create first invoice with product line
     * 2. Create second invoice with the same product line UUID
     * 3. Expect exception when saving second invoice
     * 4. Verify transaction rolled back
     */
    public function testSaveRollsBackWhenProductLineFails(): void
    {
        $this->expectException(QueryException::class);

        $duplicatedUuid = Uuid::uuid7();

        $firstInvoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'First Invoice',
            customerEmail: 'first@example.com',
        );

        $firstInvoice->addProductLine(new InvoiceProductLine(
            id: $duplicatedUuid,
            name: 'Product 1',
            quantity: 1,
            unitPrice: Money::of(1000, 'PLN'),
        ));

        // given
        $this->repository->save($firstInvoice);

        $secondInvoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'Second Invoice',
            customerEmail: 'second@example.com',
        );

        $secondInvoice->addProductLine(new InvoiceProductLine(
            id: $duplicatedUuid,
            name: 'Product 2',
            quantity: 1,
            unitPrice: Money::of(1000, 'PLN'),
        ));

        // when
        $this->repository->save($secondInvoice);

        // then
        $this->assertDatabaseMissing('invoices', [
            'customer_name' => 'Second Invoice',
        ]);
    }
}
