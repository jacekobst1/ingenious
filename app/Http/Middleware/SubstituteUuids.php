<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Reflector;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionParameter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

final class SubstituteUuids
{
    /**
     * @throws BadRequestException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        /** @var ReflectionParameter[] $parameters */
        $parameters = array_filter($route->signatureParameters(), function ($p) {
            return Reflector::getParameterClassName($p) === UuidInterface::class;
        });

        foreach ($parameters as $parameter) {
            $parameterValue = $route->parameter($parameter->getName());

            try {
                $parameterUuidValue = Uuid::fromString($parameterValue);
            } catch (InvalidUuidStringException) {
                return response()->json(
                    ['message' => "Invalid UUID string: $parameterValue"],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $route->setParameter($parameter->getName(), $parameterUuidValue);
        }

        return $next($request);
    }
}
