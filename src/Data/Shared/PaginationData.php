<?php

namespace Maxiviper117\Paystack\Data\Shared;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class PaginationData extends Data
{
    public function __construct(
        public ?string $next = null,
        public ?string $previous = null,
        public int $perPage = 0,
        public ?int $total = null,
        public ?int $currentPage = null,
        public ?int $pageCount = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            next: Payload::nullableString($payload, 'next'),
            previous: Payload::nullableString($payload, 'previous'),
            perPage: Payload::int($payload, 'perPage', Payload::int($payload, 'per_page')),
            total: array_key_exists('total', $payload) ? Payload::int($payload, 'total') : null,
            currentPage: array_key_exists('page', $payload) || array_key_exists('current_page', $payload)
                ? Payload::int($payload, 'page', Payload::int($payload, 'current_page'))
                : null,
            pageCount: array_key_exists('pageCount', $payload) || array_key_exists('last_page', $payload)
                ? Payload::int($payload, 'pageCount', Payload::int($payload, 'last_page'))
                : null,
        );
    }
}
