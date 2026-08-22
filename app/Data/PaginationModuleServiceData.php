<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

/**
 * @template TQuery of Data
 */
#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class PaginationModuleServiceData extends PaginationRequestData
{
    public function __construct(
        public int|Optional|null $page = 20,
        public int|Optional|null $limit = 1,
        public string|Optional|null $sortBy = null,
        public string|Optional|null $sortDirection = null,

        /** @var TQuery|Optional|null */
        public Data|Optional|null $dataQuery = null,
    ) {
        parent::__construct($page, $limit, $sortBy, $sortDirection);
    }
}
