The proposal
====================
This library proposes a solution to the problem about :ref:`cqrs-es-gdpr-recap`.

Event Store
------------
Instead of thinking in terms of deleting or modifying events, the idea is to persist from the very beginning of the
history, events in which payload (user information, or in general the event containing sensitive data) is encrypted by
an encryption key specific for each Aggregate. As long as the key is present, the data can be encrypted and decrypted.
When the key will be deleted (following a user request), the events will remain in the Event Store, but the payload,
originally encrypted, will remain encrypted without the possibility of decryption. Thus, the story will remain
unchanged, but the data is not understandable.

.. tabs::

   .. tab:: Normal payload

      .. code:: json

            {
                "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
                "payload": {
                    "id": "b0fce205-d816-46ac-886f-06de19236750",
                    "name": "Matteo",
                    "surname": "Galacci",
                    "email": "m.galacci@gmail.com"
                    "occurred_at": "2022-01-08T14:22:38.065+00:00",
                }
            }

   .. tab:: Sensitized payload

      .. code:: json

            {
                "class": "SensitiveUser\\User\\Domain\\Event\\UserRegistered",
                "payload": {
                    "id": "b0fce205-d816-46ac-886f-06de19236750",
                    "name": "Matteo",
                    "surname": "#-#2Iuofg4NKKPLAG2kdJrbmQ==:bxQo+zXfjUgrD0jHuht0mQ==",
                    "email": "#-#OFLfN9XDKtWrmCmUb6mhY0Iz2V6wtam0pcqs6vDJFRU=:bxQo+zXfjUgrD0jHuht0mQ==",
                    "occurred_at": "2022-01-08T14:22:38.065+00:00",
                }
            }

Projections
------------

The sensitization operation is performed at a different time from the event projection, so the views will have the data
decrypted to allow the read operations to work correctly. When a user makes use of the right to be forgotten, you
should do three things:

1. Delete his encryption key
2. Delete the views that contain his data
3. Re-project events to regenerate views with encrypted data. (This will be easy as since there is no encryption key for a specific Aggregate, reading it from the Event Store will be hydrated with the sensitized data. This obviously involves :ref:`particular checks <sensitive-data-manager-sub-mod-label>` in the Value Objects or in the Aggregate itself)

.. figure:: /imgs/broadway-sensitive-serializer-es-representation.svg
   :width: 100%
   :align: center
   :alt: Broadway sensitive serializer event store representation

   Broadway sensitive serializer event store representation

Important note
--------------

**It's important understand that the idea behind this project is not about general security or data leak. The idea
behind this implementation is rather to make a CQRS + ES system compliant with the user's right of asking at any time
to be forgotten, while keeping the system consistent.**

Of course, you can use this library also in a different context of GDPR law, since that basically this library does
nothing but decorate Broadway serializer, giving it the ability to encrypt and decrypt payload of the events.