Broadway concepts and proposal
================================

Aggregate and persistence
------------------------------

Since this wiki is not meant to be a complete manual on the concepts in question, we will just call the Model,
Aggregate and remind ourselves that the Aggregate, as such, is the source of our domain events; a client will ask
the ``User`` Aggregate to create a new user, which will not only create the instance, but also the related
event, ``UserCreated``.

.. code-block:: php

    class BroadwayUsers extends EventSourcingRepository implements Users
    {
        public function add(User $user): void
        {
            parent::save($user);
        }
    }

    class User extends EventSourcedAggregateRoot
    {
        public static function crea(
                UserId $userId,
                string $name,
                string $surname,
                string $email,
                DateTimeImmutable $regDate
        ): self
        {
            $user = new self();

            $user->apply(new UserCreated($userId, $name, $surname, $email, $regDate));

            return $user;
        }
    }

    $user = User::create($userId, $name, $surname, $email, $registrationDate);

    $users->add($user);

Event serialization
------------------------

When we ask Broadway to persist an Aggregate, the EventSourcingRepository takes all the events not yet committed from
the Aggregate and asks the specific implementation of the Event Store to serialize them and then save them.
For example, in the case of Broadway `DBALEventStore: <https://github.com/broadway/event-store-dbal>`_

.. code-block:: php

    private function insertMessage(Connection $connection, DomainMessage $domainMessage): void
    {
        $data = [
            'uuid' => $this->convertIdentifierToStorageValue((string) $domainMessage->getId()),
            'playhead' => $domainMessage->getPlayhead(),
            'metadata' => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            'payload' => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),    // <-----
            'recorded_on' => $domainMessage->getRecordedOn()->toString(),
            'type' => $domainMessage->getType(),
        ];

        $connection->insert($this->tableName, $data);
    }

When instantiating the ``EventSourcingRepository`` you need to inject a serializer (``$this->payloadSerializer``) which
in the case of the default Broadway implementation is a ``SimpleInterfaceSerializer`` which implements the
``Broadway\Serializer`` interface. ``SimpleInterfaceSerializer`` does nothing but call the ``Broadway\Serializer::serialize($object): array``
or ``Broadway\Serializer::deserialize(array $serializedObject)`` method on the event to be serialized in the case of
reading from the Event Store, where it is necessary to recreate the event starting from the payload.

Let's focus for now on the ``Broadway\Serializer::serialize($object): array`` method which, as read from the signature,
returns an array which is later converted to json thanks to PHP's ``json_encode()`` function.

Proposal implementation
------------------------

It is precisely on the serializer that this library intervenes. The idea is to decorate the native Broadway
serialization by adding the ability to encrypt and decrypt (sensitize and desensitize) the payloads of the events, or
rather the values of its keys, based on 3 strategies that we will see later, ``Whole strategy``, ``Partial Strategy`` and
``Custom strategy``. Therefore, when a new aggregate is created, a specific key will be generated which will be used
to encrypt and decrypt.

.. tabs::
   .. tab:: DBAL payload
      .. code:: json

        {
            "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
            "payload": {
                "id": "446effc9-4f5c-4369-8e89-91cb5c8509b9",
                "occurred_at": "2022-01-08T14:22:38.065+00:00",
                "name": "Matteo",
                "surname": "Galacci",
                "email": "m.galacci@gmail.com"
            }
        }

   .. tab:: Whole strategy
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

   .. tab:: Partial strategy
      .. code-block:: json

            {
                "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
                "payload": {
                    "email": "#-#jTYqDtzJ8HHabEnJMMtuaiwiFcmCkZzel5985nSf\/Ig=:iEMqT4YFE7OQzKdClNaDUg==",
                    "id": "96607c7a-f4cd-4dd7-a406-9cde00913f79",
                    "name": "Dario",
                    "occurred_at": "2022-01-14T15:04:58.323+00:00",
                    "surname": "#-#SXZXQsvLTCVX8Kel0yaoHg==:iEMqT4YFE7OQzKdClNaDUg=="
                }
            }

   .. tab:: Custom strategy
      .. code-block:: json

            {
                "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
                "payload": {
                    "id": "c9298698-b30e-40c5-8d85-624fdf57f9df",
                    "occurred_at": "2022-01-08T14:26:39.483+00:00",
                    "name": "Matteo",
                    "surname": "Galacci",
                    "email": "#-#aw+tw7shnEs2px030QS9WgRmGZckEGnIeR0a8ByMkPI=:Q0jkEOZtOs56tMkc8SjP5g=="
                }
            }

Double key encryption
-----------------------

As mentioned above, when a new Aggregate is created, its key is also created and persisted in the appropriate table.
Each aggregate has its own key so that it can invalidate individual Aggregates upon request. To improve security, the
key of the aggregate, which we will call ``AGGREGATE_KEY``, is in turn encrypted with what we will
call ``AGGREGATE_MASTER_KEY``.

AGGREGATE_KEY
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- It persisted in the database and is in a 1:1 relationship with the aggregate.
- It is encrypted with the ``AGGREGATE_MASTER_KEY``. This is to prevent events from being decrypted following a database violation.
- It can be deleted so as to make the Aggregate no longer decryptable

AGGREGATE_MASTER_KEY
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- It is one for all ``AGGREGATE_KEY``.
- It is not persisted in the database. It is set in an environment variable or otherwise on the server. More drivers will be available in the future to get the key.
