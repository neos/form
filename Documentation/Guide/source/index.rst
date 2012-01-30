=========================
Form API and Form Builder
=========================

Contents:

.. toctree::
   :maxdepth: 2

* This Doc: Tutorial-style, with references to in-depth API

Purpose
=======

* what is the form api, what is the form builder
* what problem does it try to solve?

Quickstart
==========

**10-minute introduction**

* Give a simple example using custom Form Factory, rendering and creating a form
* introduce Form Definition, Pages, Form Elements, Finishers (small example, link to API doc for full reference)
* TODO in API Doc: Reduced Class Diagramm
* Outlook: adjusting form output

Adjusting Form Output
=====================

**How can form output be modified without programming?**

* Create a sub-preset inheriting from main preset
* Introduce Form Presets and Form Element Types sortly
* Override Fluid Template Paths for a specific form element (like Textbox)
* Using supertypes for creating new form element
* Change global partial path or global layout path

Extending Form API
==================

**How can the form output be modified with programming?**

* Create PHP-based renderer for a form element
* Create custom PHP-based form element implementation
* Signals / Slots verwenden (Kontrollfluss des Forms steuern)

Configuring Form Builder
========================

**Configure Form Builder through settings**

* "Create Elements" umgruppieren
* Create Sub-element of text with Required Validator

Extending Form Builder
======================

**How can the form builder be adjusted**

* Override Form Builder Handlebars Template
* Create custom editor in JS
* Create custom Validator Editor in JS

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

