Modules
======================================================================

The library consists of 2 Modules, ``DataManager`` and ``Serializer``.

Architectural diagram
----------------------

.. figure:: /imgs/broadway-sensitive-serializer-architecture.svg
   :width: 100%
   :align: center
   :alt: Broadway sensitive serializer architecture

   Broadway sensitive serializer architecture

Data manager
----------------------

``DataManager`` module deals with data encryption and decryption, the creation of the ``AGGREGATE_KEY`` and the
orchestration of the logics related to the sensitization and desensitization of events.

.. _sensitive-data-manager-sub-mod-label:

`SensitiveDataManager <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Service/SensitiveDataManager.php>`_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is the interface for string encryption and decryption services. It asks for the implementation of the ``doEncrypt``
and ``doDecrypt`` methods, in which to implement your own concrete logic. The interface also provides the public
constant `SensitiveDataManager::IS_SENSITIZED_INDICATOR <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Service/SensitiveDataManager.php#L13>`_
which is used as a prefix in encrypted strings in order to understand if a string is in the clear or not.
This check can be done with the `SensitiveTool::isSensitized(string $data): bool <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Service/SensitiveTool.php#L17-L20>`_ tool. Very convenient when it is necessary to carry out validations
in the hydration phase, for example of a Value Object or an Aggregate.

The library provides an implementation of this interface that uses the AES256 algorithm: `AES256SensitiveDataManager <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Infrastructure/Domain/Service/AES256SensitiveDataManager.php>`_

`KeyGenerator <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Service/KeyGenerator.php>`_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is the interface for the ``AGGREGATE_KEY`` creation services. Asks for the implementation of the ``generate`` method.

The library provides an implementation of this interface based on openssl: `OpenSSLKeyGenerator <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Infrastructure/Domain/Service/OpenSSLKeyGenerator.php>`_

`AggregateKeys <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Aggregate/AggregateKeys.php>`_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is the interface to the repository that takes care of the persistence of the ``AGGREGATE_KEY`` through Model
`AggregateKey <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Aggregate/AggregateKey.php>`_.
It asks for the implementation of the ``add``, ``withAggregateId`` and ``update`` methods.

A DBAL-based implementation is available by installing the `Broadway Sensitive Serializer DBAL <https://github.com/matiux/broadway-sensitive-serializer-dbal>`_
library.

Serializer
----------------------

`BroadwaySerializerDecorator <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/BroadwaySerializerDecorator.php>`_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is the abstract class that represents the original Broadway serializer decorator. It implements the
`Broadway's serializer <https://github.com/broadway/broadway/blob/master/src/Broadway/Serializer/Serializer.php>`_
interface and depends on an implementation of  Broadway's serializer.

`SensitiveSerializer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/SensitiveSerializer.php>`_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is the concrete serializer implemented by the library. Extends Broadway\Serializer\Serializer and depends
on a `Broadway\Serializer\Serializer <https://github.com/broadway/broadway/blob/master/src/Broadway/Serializer/Serializer.php>`_
object (you can pass the standard Broadway serializer, `SimpleInterfaceSerializer <https://github.com/broadway/broadway/blob/master/src/Broadway/Serializer/SimpleInterfaceSerializer.php>`_)
and a SensitizerStrategy object.

Sensitization strategies
---------------------------

The library provides three different types of sensitization for the events payload, ``Whole``, ``Partial`` and ``Custom``.

Whole strategy
~~~~~~~~~~~~~~~~~

The Whole strategy aims to encrypt all the keys of the event payload with the exception of the aggregate id and the date
of issue of the event.

.. code-block:: json

    {
        "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
        "payload": {
            "email": "#-#OFLfN9XDKtWrmCmUb6mhY0Iz2V6wtam0pcqs6vDJFRU=:bxQo+zXfjUgrD0jHuht0mQ==",
            "id": "b0fce205-d816-46ac-886f-06de19236750",
            "name": "#-#EXWLg\/JANMK\/M+DmlpnOyQ==:bxQo+zXfjUgrD0jHuht0mQ==",
            "occurred_at": "2022-01-08T14:25:13.483+00:00",
            "surname": "#-#2Iuofg4NKKPLAG2kdJrbmQ==:bxQo+zXfjUgrD0jHuht0mQ=="
        }
    }

The reference class for this strategy is `WholePayloadSensitizer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/WholeStrategy/WholePayloadSensitizer.php>`_. While the client class of the strategy is
`WholeStrategy <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/WholeStrategy/WholeStrategy.php>`_.
This class depends on the ``WholePayloadSensitizer`` and the `WholePayloadSensitizerRegistry <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/WholeStrategy/WholePayloadSensitizerRegistry.php>`_
registry which must be initialized with a ``class-string[]`` containing the list of FQCN (Full Qualified Class Name) of the events that
you want to make subject to encryption. This therefore implies that not all events will be encrypted, but it can be
selected selectively by populating the register.

**Keys exclusion**

The ``id`` key of the Aggregate can be configured during strategy creation via the
`WholePayloadSensitizer::$excludedIdKey <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/WholeStrategy/WholePayloadSensitizer.php#L37>`_
attribute. In the same way it is possible to indicate a list of keys to be excluded from encryption using the
`WholePayloadSensitizer::$excludedKeys <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/WholeStrategy/WholePayloadSensitizer.php#L36>`_
attribute.

**Run whole strategy example** `example/WholeStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/WholeStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/WholeStrategy/example.php

Partial strategy
~~~~~~~~~~~~~~~~~

The partial strategy, probably the most convenient, involves the selective and parameterized encryption of a
payload. It will be sufficient to pass to the `PartialPayloadSensitizerRegistry <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PartialStrategy/PartialPayloadSensitizerRegistry.php>`_
register an array with the events to be encrypted and for each event, indicating the keys:

The client class of the strategy is `PartialStrategy <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PartialStrategy/PartialStrategy.php>`_
which is dependent on the ``PartialPayloadSensitizerRegistry`` and `PartialPayloadSensitizer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PartialStrategy/PartialPayloadSensitizer.php>`_.

.. code-block:: php

    $events = [
       MyEvent::class => ['email', 'surname'],
       MySecondEvent::class => ['address'],
    ];

    new PartialPayloadSensitizerRegistry($events);

**Run partial strategy example** `example/PartialStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/PartialStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/PartialStrategy/example.php

Custom strategy
~~~~~~~~~~~~~~~~~

The Custom strategy involves the creation of specific ``Sensitizers`` in order to sensitize only a part of the payload
according to the needs. These Sensitizers extend the abstract class `PayloadSensitizer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php>`_
which involves the implementation of the `PayloadSensitizer::generateSensitizedPayload(): array <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php#L112>`_
and `PayloadSensitizer::generateDesensitizedPayload(): array <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php#L132>`_
methods.

Once defined, the Sensitizers must be used to initialize the specific `CustomPayloadSensitizerRegistry <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/CustomStrategy/CustomPayloadSensitizerRegistry.php>`_
registry of this strategy.

The client class of the strategy is `CustomStrategy <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/CustomStrategy/CustomStrategy.php>`_
which is solely dependent on the ``CustomPayloadSensitizerRegistry``. An example of implementation is present in the `test <https://github.com/matiux/broadway-sensitive-serializer/blob/master/tests/Integration/SensitiveSerializer/Serializer/Strategy/CustomStrategy/CustomStrategyTest.php#L167-L220>`_.

**Run custom strategy example** `example/CustomStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/CustomStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/CustomStrategy/example.php

Strategy summary
~~~~~~~~~~~~~~~~~

- With the Whole Strategy you can decide what not to encrypt if necessary, but not for a single event; you can exclude keys for all events subject to sensitization.
- With the Partial Strategy you define the events you want to encrypt, and for each event you define the list of keys to be excluded, using a simple array.
- With the Custom Strategy you have full control over how to intervene on the payload.

Value Serializer
----------------------

`PayloadSensitizer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php>`_
uses a `value serializer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/ValueSerializer/ValueSerializer.php>`_
respecting the ValueSerializer interface. This Serializer implements ``strategy pattern`` to be able to chose which type
of serialization use. Broadway sensitive serializer provides a `JsonValueSerializer <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/ValueSerializer/JsonValueSerializer.php>`_
implementation to serialize this types: ``scalar``, ``null``, ``array``

AggregateKey model creation
--------------------------------------------

The `PayloadSensitizer::$automaticAggregateKeyCreation <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php#L41>`_
parameter determines if the `AggregateKey model <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/DataManager/Domain/Aggregate/AggregateKey.php>`_
should be created automatically at serialization, or if you want to create it manually. The existence check of the model
is not carried out in the `PayloadSensitizer::desensitize(array $serializedObject): array <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php#L114-L130>`_
method as it would be a contradiction; the process of saving events starts with the saving and relative serialization
of a first event, so when calling the desensitize method it is assumed that the AggregateKey has already been created.
Otherwise an exception will be throw.

Automatic creation
~~~~~~~~~~~~~~~~~~~~~

In this mode the ``AggregateKey`` model, if it does not exist, is created when calling method
`PayloadSensitizer::sensitize(array $serializedObject): array <https://github.com/matiux/broadway-sensitive-serializer/blob/master/src/SensitiveSerializer/Serializer/Strategy/PayloadSensitizer.php#L56-L72>`_.
The key is created if it does not exist, otherwise it uses the existing one:

.. code-block:: php

    $decryptedAggregateKey = $this->automaticAggregateKeyCreation ?
       $this->createAggregateKeyIfDoesNotExist($aggregateId) :
       $this->obtainDecryptedAggregateKeyOrError($aggregateId);

Manual creation
~~~~~~~~~~~~~~~~~~~~~

In this mode the ``AggregateKey`` model must exist, if it doesn't, an exception will be raised. This mode involves
creating the model in advance. The most convenient time may be during the creation of the Aggregate.

.. code-block:: php

    $aggregateKeyManager->createAggregateKey($userId);

    $user = User::create($userId, $name, $surname, $email, $registrationDate);

    $users->add($user);

Generally speaking, the correct way to handle this in both ways would be to run the domain service atomically, within a
transaction. The `ddd-starter-pack <https://github.com/matiux/ddd-starter-pack>`_
library provide some convenient abstractions to handle this:
`TransactionalApplicationServiceTest <https://github.com/matiux/ddd-starter-pack/blob/v3/tests/Integration/DDDStarterPack/Service/Application/TransactionalApplicationServiceTest.php>`_