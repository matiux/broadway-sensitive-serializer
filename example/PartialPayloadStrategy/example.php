<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\PartialPayloadStrategy;

require_once dirname(__DIR__).'/../vendor/autoload.php';
require_once dirname(__DIR__).'/Shared/dependencies.php';

use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\User;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserRegistered;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway\BroadwayUsers;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway\SerializedInMemoryEventStore;
use Matiux\Broadway\SensitiveSerializer\Serializer\SensitiveSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\PartialPayloadStrategy\PartialPayloadSensitizerStrategy;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Initialize specific dependencies.
 */
$sensitizers = [
    new UserRegisteredSensitizer($dataManager, $aggregateKeyManager, true),
];

$registry = new PartialPayloadSensitizerRegistry($sensitizers);

$partialSensitizerStrategy = new PartialPayloadSensitizerStrategy($registry);

$serializer = new SensitiveSerializer(
    new SimpleInterfaceSerializer(),
    $partialSensitizerStrategy
);

$inMemoryEventStore = new TraceableEventStore(new InMemoryEventStore());
$inMemoryEventStore->trace();

$users = new BroadwayUsers(
    new SerializedInMemoryEventStore($inMemoryEventStore, $serializer),
    $eventBus
);

/**
 * Usage.
 */
$userId = UserId::create();

$user = User::create(
    $userId,
    'Matteo',
    'Galacci',
    'm.galacci@gmail.com',
    new DateTimeRFC()
);

$users->add($user);

/**
 * Let's take a look at Event Store.
 */

/** @var UserRegistered $userRegistered */
$userRegistered = current($inMemoryEventStore->getEvents());

Assert::count($inMemoryEventStore->getEvents(), 1);
Assert::isInstanceOf($userRegistered, UserRegistered::class);

$serialized = $userRegistered->serialize();

Assert::true(SensitiveTool::isSensitized($serialized['email'])); // This is the only key encrypted, as indicated in UserRegisteredSensitizer.php
Assert::false(SensitiveTool::isSensitized($serialized['name']));
Assert::false(SensitiveTool::isSensitized($serialized['surname']));
Assert::false(SensitiveTool::isSensitized($serialized['id']));
Assert::false(SensitiveTool::isSensitized($serialized['occurred_at']));

/**
 * And now let's take a look to the AggregateKeys repository.
 * You will notice that model has been auto generated thanks third parameter of UserRegisteredSensitizer set to true.
 */
$aggregateKey = $aggregateKeys->withAggregateId(Uuid::fromString((string) $userId));
Assert::true($aggregateKey->exists());

/**
 * Loading aggregate from Event Store, its payload will be decrypted, if its AggregateKey exists.
 */
$user = $users->load($userId);

Assert::false(SensitiveTool::isSensitized($user->email())); // Now the email is in clear

/**
 * If the key does not exist, the decryption will not work.
 */
$aggregateKey = $aggregateKeys->withAggregateId(Uuid::fromString((string) $userId));
$aggregateKey->delete();

$aggregateKeys->update($aggregateKey);

$user = $users->load($userId);

Assert::true(SensitiveTool::isSensitized($user->email())); // Email is encrypted