<?php

declare(strict_types=1);

namespace Matiux\Broadway\SensitiveSerializer\Example\Shared;

use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use Matiux\Broadway\SensitiveSerializer\DataManager\Domain\Service\AggregateKeyManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Aggregate\InMemoryAggregateKeys;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\AES256SensitiveDataManager;
use Matiux\Broadway\SensitiveSerializer\DataManager\Infrastructure\Domain\Service\OpenSSLKeyGenerator;

require_once dirname(__DIR__).'/../vendor/autoload.php';

/**
 * Initialize generic dependencies.
 */
$dataManager = new AES256SensitiveDataManager();
$keyGenerator = new OpenSSLKeyGenerator();
$aggregateKeys = new InMemoryAggregateKeys();
$aggregateKeyManager = new AggregateKeyManager($keyGenerator, $aggregateKeys, $dataManager, Key::AGGREGATE_MASTER_KEY);
$eventBus = new TraceableEventBus(new SimpleEventBus());
$eventBus->trace();
