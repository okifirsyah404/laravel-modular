<?php

namespace App\Contracts\Services;

use App\Data\PaginationModuleServiceData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * @template TData of Data
 */
interface ModuleServiceInterface
{
    /**
     * @param Data $data
     * @return TData
     */
    public function create(Data $data): Data;

    /**
     * @param string|int $id
     * @return TData|null
     */
    public function getById(string|int $id): ?Data;

    /**
     * @param array $ids
     * @return DataCollection
     */
    public function getByIds(array $ids): DataCollection;

    /**
     * @param PaginationModuleServiceData $paginationRequestData
     * @return PaginatedDataCollection
     */
    public function paginate(PaginationModuleServiceData $paginationRequestData): PaginatedDataCollection;

    /**
     * @param Data $data
     * @return TData
     */
    public function update(Data $data): Data;

    /**
     * @param Data $data
     * @return void
     */
    public function delete(Data $data): void;
}
