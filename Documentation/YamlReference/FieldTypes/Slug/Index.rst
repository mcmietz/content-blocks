.. include:: /Includes.rst.txt
.. _field_type_slug:

====
Slug
====

The :yaml:`Slug` type generates a slug field, which generates a unique string
for the record.

Settings
========

..  confval-menu::
    :name: confval-slug-options
    :display: table
    :type:
    :default:
    :required:

.. confval:: eval
   :name: slug-eval
   :required: false
   :type: string

   :yaml:`unique`, :yaml:`uniqueInSite` or :yaml:`uniqueInPid`.

.. confval:: generatorOptions
   :name: slug-generatorOptions
   :required: false
   :type: array

   Options related to the generation of the slug. Keys:

   fields (array)
      An array of fields to use for the slug generation. Adding multiple fields
      to the simple array results in a concatenation. In order to have fallback
      fields, a nested array must be used.

   Example:

   .. code-block:: yaml

      generatorOptions:
        fields:
          - header

Examples
========

Minimal
-------

.. code-block:: yaml

    name: example/slug
    fields:
      - identifier: slug
        type: Slug
        eval: unique
        generatorOptions:
          fields:
            - header

Advanced / use case
-------------------

.. code-block:: yaml

    name: example/slug
    fields:
      - identifier: slug
        type: Slug
        eval: unique
        generatorOptions:
          fields:
            -
              - header
              - fallbackField
            - date
