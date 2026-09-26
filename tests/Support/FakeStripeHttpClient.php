<?php

declare(strict_types=1);

namespace Tests\Support;

use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

/**
 * In-memory Stripe API: records every request and answers the endpoints the
 * signup flow uses, so tests assert on the real payloads sent to Stripe.
 */
class FakeStripeHttpClient implements ClientInterface
{
    /**
     * @var list<array{method: string, path: string, params: array<string, mixed>}>
     */
    public array $requests = [];

    public bool $failCheckout = false;

    public static function install(): self
    {
        $client = new self;

        ApiRequestor::setHttpClient($client);

        return $client;
    }

    public static function uninstall(): void
    {
        ApiRequestor::setHttpClient(null);
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
    {
        $path = (string) parse_url($absUrl, PHP_URL_PATH);

        $this->requests[] = ['method' => strtoupper($method), 'path' => $path, 'params' => $params ?? []];

        return match (true) {
            $path === '/v1/customers' => $this->respond([
                'id' => 'cus_test_123',
                'object' => 'customer',
                'email' => $params['email'] ?? null,
            ]),
            str_starts_with($path, '/v1/customers/') => $this->respond([
                'id' => basename($path),
                'object' => 'customer',
            ]),
            $path === '/v1/checkout/sessions' && $this->failCheckout => $this->respond([
                'error' => ['type' => 'api_error', 'message' => 'Stripe is down'],
            ], 500),
            $path === '/v1/checkout/sessions' => $this->respond([
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ]),
            $path === '/v1/billing_portal/sessions' => $this->respond([
                'id' => 'bps_test_123',
                'object' => 'billing_portal.session',
                'url' => 'https://billing.stripe.com/p/session/test_123',
            ]),
            default => $this->respond(['error' => ['type' => 'invalid_request_error', 'message' => "Unhandled {$path}"]], 404),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastRequestTo(string $path): ?array
    {
        return collect($this->requests)->last(fn (array $request): bool => $request['path'] === $path);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{0: string, 1: int, 2: array<string, string>}
     */
    protected function respond(array $body, int $status = 200): array
    {
        return [json_encode($body, JSON_THROW_ON_ERROR), $status, ['request-id' => 'req_test']];
    }
}
