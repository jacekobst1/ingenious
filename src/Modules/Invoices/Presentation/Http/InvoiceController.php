<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceRequest;
use Modules\Invoices\Application\Services\InvoiceCreator;
use Modules\Invoices\Application\Services\InvoiceFinder;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\HttpFoundation\Response;

final readonly class InvoiceController
{
    public function show(string $id, InvoiceFinder $finder): JsonResponse
    {
        // TODO change to UuidInteface url param
        try {
            $uuid = Uuid::fromString($id);
        } catch (InvalidUuidStringException) {
            return new JsonResponse(
                data: [
                    'message' => 'Invalid invoice ID format.',
                ],
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $invoice = $finder->find($uuid);

            return new JsonResponse(
                data: $invoice,
                status: Response::HTTP_OK,
            );
        } catch (InvoiceNotFoundException $e) { // TODO handle the exception in Handler
            return new JsonResponse(
                data: ['message' => $e->getMessage()],
                status: Response::HTTP_NOT_FOUND,
            );
        }
    }

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
