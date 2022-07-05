Limitations
======================

Existing CQRS + ES projects
---------------------------------

If you have read the previous pages you will have understood that the idea behind this library is to create sensitized
events from the beginning. Obviously I am not referring to all events, but to those containing sensitive data. If you
have existing projects, with an Event Store that contains events where sensitive data is in the clear, as we said,
creating compensation events will not serve you as the story remains clear. If you find yourself in this situation, the
only way I can advise you for now, with all the relative contraindications, is to take all your events and migrate them
to a new Event Store by execute a sensitization action in the middle, via the :ref:`modules:Data manager` module.
By doing this you will have a new Event Store, the same as the old one, but with encrypted sensitive data.
From now on you will be able to use the library normally for the new events that will be generated.

Future idea
~~~~~~~~~~~~~~~~~~

One idea for the future is to create a migration module in order to simplify the idea discussed above. With a simple
configuration, you could automate the creation of a new Event Store.

.. code-block:: php

    $eventsToSensitise = [
        UserRegistered::class => [
            'email',
            'surname',
        ],
        PersonalDataAdded::class => [
            'religion',
        ]
    ];

    $eventStoreMigrator->execute($eventsToSensitise);