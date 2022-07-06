Basic concepts
--------------

CQRS
~~~~~~
CQRS (Command Query Responsibility Segregation) is a pattern that aims to separate responsibilities
for queries and for commands. It is a pattern that separates the read and write operations on a given model.
This, in practice, leads to different concrete objects, separated in write models and read models.
So, this pattern can lead to different tables or data stores where the data is separated based on whether it is
a command (write) or a query (read). Separation apart, the last state of the model will still be persisted as it
happens in traditional CRUD systems.

Event Sourcing
~~~~~~~~~~~~~~~~~~
ES (Event Sourcing), used together with CQRS, "transforms" the writing part of the CQRS models into a succession
of events that are persisted in an Event Store, a specific table or data store that acts as a chronological and
immutable register of events. The idea is that commands executed on a model lead to the issue of events which are
stored in the Event Store. In this table are persisted all events issued by a Model, with a specific incremental
index, sometimes called `playhead`, that represents order in which the events have been issued; for each new Model
(Aggregate), its events starts with `playhead = 0`. Event Store is therefore recording system for all events that,
if re-applied to the model in same order of generation, bring it to its last state. Or it might be possible to see
a previous status of a model. These events then, if listened to specific Listeners, can project views (Read Models)
or generate new commands (Processor). The views will then be the models (persisted in tables other than the Event
Store or even a different Data Store) used by the read queries.

.. figure:: /imgs/cqrs-es-diagram.png
   :width: 100%
   :align: center
   :alt: CQRS+ES diagram

   `CQRS+ES diagram from Microsoft's website <https://docs.microsoft.com/en-us/azure/architecture/patterns/event-sourcing>`_

Event Store immutability
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
So using whole CQRS+ES pattern, we have an Event Store in which all events will be written in chronological order and
grouped for each model  using aggregate id. Event Store is immutable by its nature; after writing an event, it can
never change. If necessary, compensation events will be issued to compensate the previous events. Imagine a bank
account and its list of transaction, and think of a compensation event as a reversal.

Projections
~~~~~~~~~~~~~~~~~~
In a CQRS+ES system there are usually projections. If the event store is the chronological register of all the writing
operations that took place on a specific Aggregate, then a projection is a specific view of the data; for a single
Aggregate we could have as many views as there are our needs. So, after an event is issued, an event listener could
listen that event in order to project a view of it. Multiple event listeners can listen same event to project different
representations of the same data set.

Art. 17 GDPR -Right to be forgotten
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The lay says: The data subject shall have the right to obtain from the controller the erasure of personal data
concerning him or her without undue delay and the controller shall have the obligation to erase personal data
without undue delay... `Read the complete legislation <https://gdpr-info.eu/art-17-gdpr/>`_

.. _cqrs-es-gdpr-recap:

CQRS+ES+GDPR
~~~~~~~~~~~~~~~~~~~~~~~~
We have said that in CQRS+ES pattern the Event Store is immutable and we have also said that to be compliant with the
GDPR, a user can be request cancellation of his data. Thus said it seems a paradox, right? Because deleting user's data
in a CQRS+ES system would mean either deleting events from Event Store or modifying existing events. Both things we
cannot do. Compensation events cannot useful in this case as by going back in history, we could always recover user's
data.