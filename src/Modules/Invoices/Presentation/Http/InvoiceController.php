<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceRequest;
use Modules\Invoices\Application\Dtos\Requests\SendInvoiceRequest;
use Modules\Invoices\Application\Services\InvoiceCreator;
use Modules\Invoices\Application\Services\InvoiceFinder;
use Modules\Invoices\Application\Services\InvoiceSender;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class InvoiceController
{
    public function show(UuidInterface $id, InvoiceFinder $finder): JsonResponse
    {
        $invoice = $finder->find($id);

        return new JsonResponse(
            data: $invoice,
            status: Response::HTTP_OK,
        );
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

    /**
     * @throws Exception
     */
    public function send(UuidInterface $id, SendInvoiceRequest $data, InvoiceSender $sender): JsonResponse
    {
        $sender->send($id, $data);

        return new JsonResponse(
            data: ['message' => 'success'],
            status: Response::HTTP_OK,
        );
    }
}
