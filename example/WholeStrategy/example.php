<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\WholeStrategy;

require_once dirname(__DIR__).'/../vendor/autoload.php';

use Adbar\Dot;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\SensitiveTool;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\Email;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\User;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Aggregate\UserId;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserCreated;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\Event\UserInfo;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Domain\ValueObject\DateTimeRFC;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway\BroadwayUsers;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Infrastructure\Domain\Broadway\SerializedInMemoryEventStore;
use Matiux\Broadway\SensitiveSerializer\Example\Shared\Key;
use Matiux\Broadway\SensitiveSerializer\Serializer\SensitiveSerializer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholePayloadSensitizer;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholePayloadSensitizerRegistry;
use Matiux\Broadway\SensitiveSerializer\Serializer\Strategy\WholeStrategy\WholeStrategy;
use Matiux\Broadway\SensitiveSerializer\Serializer\ValueSerializer\JsonValueSerializer;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Initialize generic dependencies.
 */
$dataManager = new AES256SensitiveDataManager();
$keyGenerator = new OpenSSLKeyGenerator();
$aggregateKeys = new InMemoryAggregateKeys();
$aggregateKeyManager = new AggregateKeyManager($keyGenerator, $aggregateKeys, $dataManager, Key::AGGREGATE_MASTER_KEY);
$valueSerializer = new JsonValueSerializer();
$eventBus = new TraceableEventBus(new SimpleEventBus());
$eventBus->trace();

/**
 * Initialize specific dependencies.
 */
$events = [
    UserCreated::class,
];

$registry = new WholePayloadSensitizerRegistry($events);

$wholePayloadSensitizer = new WholePayloadSensitizer(
    $dataManager,
    $aggregateKeyManager,
    $valueSerializer,
    true,
    ['occurred_at', 'user_info.age'],
    'id'
);

$wholeSensitizerStrategy = new WholeStrategy($registry, $wholePayloadSensitizer);

$serializer = new SensitiveSerializer(
    new SimpleInterfaceSerializer(),
    $wholeSensitizerStrategy
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
    Email::createFromString('m.galacci@gmail.com'),
    UserInfo::create(36, 1.75, ['blonde']),
    new DateTimeRFC(),
);

$users->add($user);

/**
 * Let's take a look at Event Store.
 */

/** @var UserCreated $userCreatedEvent */
$userCreatedEvent = current($inMemoryEventStore->getEvents());

Assert::count($inMemoryEventStore->getEvents(), 1);
Assert::isInstanceOf($userCreatedEvent, UserCreated::class);

echo json_encode($userCreatedEvent->serialize());
$serializedUserCreatedEvent = new Dot($userCreatedEvent->serialize());

// All payload has been sensitized but `id`, `occurred_at` and `user_info.age`
Assert::true(SensitiveTool::isSensitized($serializedUserCreatedEvent['email']));
Assert::true(SensitiveTool::isSensitized($serializedUserCreatedEvent['name']));
Assert::true(SensitiveTool::isSensitized($serializedUserCreatedEvent['surname']));
Assert::true(SensitiveTool::isSensitized($serializedUserCreatedEvent['surname']));
Assert::true(SensitiveTool::isSensitized($serializedUserCreatedEvent->get('user_info.age')));

Assert::false(SensitiveTool::isSensitized($serializedUserCreatedEvent['id']));
Assert::false(SensitiveTool::isSensitized($serializedUserCreatedEvent['occurred_at']));

/**
 * And now let's take a look to the AggregateKeys repository.
 * You will notice that model has been auto generated thanks third parameter of UserCreatedSensitizer set to true.
 */
$aggregateKey = $aggregateKeys->withAggregateId(Uuid::fromString((string) $userId));
Assert::true($aggregateKey->exists());

/**
 * Loading aggregate from Event Store, its payload will be decrypted, if its AggregateKey exists.
 */
$user = $users->load($userId);

Assert::false(SensitiveTool::isSensitized((string) $user->email())); // Now the email is in clear

/**
 * If the key does not exist, the decryption will not work.
 */
$aggregateKey = $aggregateKeys->withAggregateId(Uuid::fromString((string) $userId));
$aggregateKey->delete();

$aggregateKeys->update($aggregateKey);

$user = $users->load($userId);

Assert::true(SensitiveTool::isSensitized($user->userInfo()->age())); // Age is encrypted
Assert::true(SensitiveTool::isSensitized((string) $user->email())); // Email is encrypted
