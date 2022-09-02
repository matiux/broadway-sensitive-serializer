##################
Examples
##################

****************
Library examples
****************

In this repository you can find three example

- :ref:`Whole strategy <whole-example>`
- :ref:`Partial strategy <partial-example>`
- :ref:`Custom strategy <custom-example>`

Of course, you will also find many ideas in the tests.

.. _whole-example:

Whole sensitization example
===========================

`example/WholeStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/WholeStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/WholeStrategy/example.php | jq

.. _partial-example:

Partial sensitization example
=============================

`example/PartialStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/PartialStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/PartialStrategy/example.php | jq

.. _custom-example:

Custom sensitization example
============================

`example/CustomStrategy <https://github.com/matiux/broadway-sensitive-serializer/tree/master/example/CustomStrategy>`_

.. code-block:: shell

    make build-php ARG="--no-cache"
    make upd
    make composer ARG="install"
    make enter
    php example/CustomStrategy/example.php | jq

************
Demo project
************

For a complete and working demo you can check out at this Symfony 6 project:
`broadway-sensitive-serializer-demo <https://github.com/matiux/broadway-sensitive-serializer-demo>`_. It is divided into
three branches:

- `whole_strategy <https://github.com/matiux/broadway-sensitive-serializer-demo/tree/whole_strategy>`_
- `partial_strategy <https://github.com/matiux/broadway-sensitive-serializer-demo/tree/partial_strategy>`_
- `custom_strategy <https://github.com/matiux/broadway-sensitive-serializer-demo/tree/custom_strategy>`_