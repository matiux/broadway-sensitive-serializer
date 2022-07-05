Examples
======================================================================

In this repository you can find three example

- :ref:`Whole strategy <whole-example>`
- :ref:`Partial strategy <partial-example>`
- :ref:`Custom strategy <custom-example>`

Of course, you will also find many ideas in the tests.

.. _whole-example:

Whole sensitization example
------------------------------

`example/WholeStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/WholeStrategy>`_

.. code-block:: shell

    ./dc up -d
    ./dc enter
    composer install
    php example/WholeStrategy/example.php | jq

.. _partial-example:

Partial sensitization example
------------------------------

`example/PartialStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/PartialStrategy>`_

.. code-block:: shell

    ./dc up -d
    ./dc enter
    composer install
    php example/PartialStrategy/example.php | jq

.. _custom-example:

Custom sensitization example
------------------------------

`example/CustomStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/CustomStrategy>`_

.. code-block:: shell

    ./dc up -d
    ./dc enter
    composer install
    php example/CustomStrategy/example.php | jq