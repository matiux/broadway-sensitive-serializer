<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate;

use Broadway\Repository\AggregateNotFoundException;

interface Users
{
    /**
     * @param UserId $id
     *
     * @throws AggregateNotFoundException
     *
     * @return User
     */
    public function byId(UserId $id): User;

    /**
     * @param User $user
     */
    public function add(User $user): void;

    /**
     * @param User $user
     */
    public function update(User $user): void;
}
