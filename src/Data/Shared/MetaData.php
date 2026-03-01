<?php

namespace Maxiviper117\Paystack\Data\Shared;

use Spatie\LaravelData\Data;

class MetaData extends Data
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?PaginationData $pagination = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $pagination = $payload === [] ? null : PaginationData::fromPayload($payload);

        return new self(
            pagination: $pagination,
            extra: $payload,
        );
    }
}
