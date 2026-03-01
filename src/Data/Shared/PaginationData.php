<?php

namespace Maxiviper117\Paystack\Data\Shared;

use Maxiviper117\Paystack\Support\Payload;
use Spatie\LaravelData\Data;

class PaginationData extends Data
{
    public function __construct(
        public int $total = 0,
        public int $perPage = 0,
        public int $currentPage = 1,
        public int $pageCount = 1,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            total: Payload::int($payload, 'total'),
            perPage: Payload::int($payload, 'perPage', Payload::int($payload, 'per_page')),
            currentPage: Payload::int($payload, 'page', Payload::int($payload, 'current_page', 1)),
            pageCount: Payload::int($payload, 'pageCount', Payload::int($payload, 'last_page', 1)),
        );
    }
}
