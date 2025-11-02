<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceRequest;
use Modules\Invoices\Application\Services\InvoiceCreator;
use Symfony\Component\HttpFoundation\Response;

final readonly class InvoiceController
{
    public function store(CreateInvoiceRequest $data, InvoiceCreator $creator): JsonResponse
    {
        $invoice = $creator->create($data);

        return new JsonResponse(
            data: [
                'message' => 'success',
                'id' => $invoice->id,
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
