<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return TModel
     */
    public function create(array $payload): Model;

    /**
     * @param  array<int, string>  $with
     * @return TModel|null
     */
    public function find(string|int $id, array $with = []): ?Model;

    /**
     * @param  array<string, mixed>|null  $where
     * @param  array<int, string>  $with
     * @return Collection<int, TModel>
     */
    public function findMany(?array $where = null, array $with = []): Collection;

    /**
     * @param  array<string, mixed>|null  $where
     * @param  array<string, mixed>  $payload
     * @return TModel|null
     */
    public function update(?array $where, array $payload): ?Model;

    /**
     * @param  array<string, mixed>|null  $where
     * @param  array<string, mixed>  $payload
     * @return Collection<int, TModel>
     */
    public function updateMany(?array $where, array $payload): Collection;

    /**
     * @param  array<string, mixed>|null  $where
     */
    public function delete(?array $where): void;

    /**
     * @return Builder<TModel>
     */
    public function queryBuilder(): Builder;
}
