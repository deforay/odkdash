<?php

namespace Application\Service;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle handler-stack middleware that records every outbound HTTP call
 * via ApiLogger. Attach with:
 *
 *   $stack = HandlerStack::create();
 *   $stack->push(new GuzzleApiLogMiddleware($apiLogger, 'sync-central-v6'));
 *   $client = new Client(['handler' => $stack, ...]);
 *
 * Per-call timing is measured around the handler — pooled / async calls
 * each get their own row. The body streams are rewound before reading
 * so downstream code still sees them at the start. On transport failure
 * (Guzzle RequestException without a Response, e.g. connect timeout) we
 * still log a row with status 0 and the error message.
 */
final class GuzzleApiLogMiddleware
{
    public function __construct(
        private readonly ApiLogger $apiLogger,
        private readonly string $source,
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $start = microtime(true);
            // If the caller is streaming the response to a file (large OData
            // pages, media downloads) don't touch the body — we'd just read
            // the same bytes back into PHP memory we were trying to avoid,
            // and risk interfering with sink-backed stream state. We still
            // log the URL + status + duration; just no body.
            $skipResponseBody = isset($options['sink']);
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($request, $start, $skipResponseBody) {
                    $this->record($request, $response, null, $start, $skipResponseBody);
                    return $response;
                },
                function ($reason) use ($request, $start, $skipResponseBody) {
                    $response = $reason instanceof RequestException ? $reason->getResponse() : null;
                    $errorMsg = $reason instanceof \Throwable ? $reason->getMessage() : (string) $reason;
                    $this->record($request, $response, $errorMsg, $start, $skipResponseBody);
                    return Create::rejectionFor($reason);
                }
            );
        };
    }

    private function record(RequestInterface $request, ?ResponseInterface $response, ?string $error, float $start, bool $skipResponseBody = false): void
    {
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        $reqBody = (string) $request->getBody();
        // Rewind so anyone downstream still gets the full body.
        if ($request->getBody()->isSeekable()) {
            $request->getBody()->rewind();
        }

        $resBody = null;
        $statusCode = 0;
        if ($response !== null) {
            $statusCode = $response->getStatusCode();
            if (!$skipResponseBody) {
                $resBody = (string) $response->getBody();
                if ($response->getBody()->isSeekable()) {
                    $response->getBody()->rewind();
                }
            }
        }

        $this->apiLogger->logCall(
            source: $this->source,
            method: $request->getMethod(),
            url: (string) $request->getUri(),
            requestBody: $reqBody !== '' ? $reqBody : null,
            statusCode: $statusCode,
            responseBody: $resBody,
            durationMs: $durationMs,
            error: $error,
        );
    }
}
