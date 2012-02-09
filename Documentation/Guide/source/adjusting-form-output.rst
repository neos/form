.. _adjusting-form-output:

Adjusting Form Output
=====================

After working through this guide, you will have learned:

* how to adjust the form output
* how to create custom Form Presets
* how to create custom form elements

Generally, this guide answers the question: **How can form output be modified without programming?**

Presets Explained
-----------------

In the :ref:`quickstart` guide, you have seen how a basic form can be built. We
will now dissect the form element creation a little more, and explain the lines
which you might not have understood yet.

Let's again look at the boilerplate code inside the form factory::

	public function build(array $factorySpecificConfiguration, $presetName) {
		$formConfiguration = $this->getPresetConfiguration($presetName);
		$form = new FormDefinition('ContactForm', $formConfiguration);
		// ...
	}

You see that the second parameter is a ``$presetName`` which is passed to
``getPresetConfiguration()``. So, let's introduce the concept of *presets* now.

A **Preset** is a container for pre-defined form configuration, and is the basic
way to adjust the form's output. Presets are defined inside the ``Settings.yaml``
file, like in the following example:

.. code-block:: yaml

	TYPO3:
	  Form:
	    Presets:
	      preset1:
	        name: 'My First Preset'
	        formElementTypes:
	          'TYPO3.Form:SingleLineTextfield':
	            # configuration for the single line textfield
	      preset2:
	        name: 'My Second Preset'
	        parentPreset: 'preset1'
	        # because preset2 *inherits* from preset1, only the changes between
	        # preset1 and preset2 need to be defined here.

The above example defines two presets (``preset1`` and ``preset2``). Because
``preset2`` defines a ``parentPreset``, it **inherits** all options from ``preset1``
if not specified otherwise.

.. tip:: The TYPO3.Form package already defines a preset with the name ``default``
   which contains all standard form elements. Look into ``TYPO3.Form/Configuration/Settings.yaml``
   for the details on the defined form elements.

   In most cases, you will create a sub-preset of the ``default`` preset, modifying
   only the parts you need.

The method ``getPresetConfiguration($presetName)`` in :api-factory:`AbstractFormFactory`
evaluates the preset inheritance hierarchy and returns a merged array of the preset
configuration.

Form Element Types Explained
----------------------------

Now that we have seen that presets can inherit from each other, let's look *inside*
the preset configuration. One particularily important part of each preset configuration
is the *form element type* definition, which configures each form element correctly.

As an example, let's create a text field with the following snippet::

	$name = $page1->createElement('name', 'TYPO3.Form:SingleLineText');
	$name->setLabel('Name');

In the above example, the form element type is ``TYPO3.Form:SingleLineText``, and
when creating the form element, it *applies all default values* being set inside
the form element type. As an example, take the following type definition:

.. code-block:: yaml

	'TYPO3.Form:SingleLineText':
	  defaultValue: 'My Default Text'
	  properties:
	    placeholder: 'My Placeholder Text'

That's exactly the same as if one wrote the following PHP code::

	$name->setDefaultValue('My Default Text');
	$name->setProperty('placeholder', 'My Placeholder Text');

So ``$page->createElement($identifier, $formElementType)`` is essentially a very
specialized *factory method*, which automatically applies the default values from
the *form element definition* on the newly created form object before returning it.

Supertypes
----------

Now, there's one more secret ingredient which makes the form framework powerful:
Every form element type can have one or multiple **supertypes**; and this
allows to only specify the differences between the "parent" form element and
the newly created one, effectively creating an inheritance hierarchy of form elements.

The following example demonstrates this:

.. code-block:: yaml


	'TYPO3.Form:SingleLineText':
	  defaultValue: 'My Default Text'
	  properties:
	    placeholder: 'My Placeholder Text'
	'TYPO3.Form:SpecialText':
	  superTypes: ['TYPO3.Form:SingleLineText']
	  defaultValue: 'My special text'

Here, the ``SpecialText`` inherits the ``placeholder`` property from the ``SingleLineText``
and only overrides the default value.

Together, presets (with parent presets) and form element types (with supertypes)
form a very flexible foundation to customize the rendering in any imaginable way,
as we will explore in the remainder of this guide.

Creating a Custom Preset
------------------------

First, we create a sub-preset inheriting from the ``default`` preset. For that,
open up ``Your.Package\Configuration\Settings.yaml`` and insert the following
contents:

.. code-block:: yaml

	TYPO3:
	  Form:
	    Presets:
	      myCustom:
	        parentPreset: 'default'

You now created a sub preset named ``myCustom`` which behaves exactly the same as
the default preset. If you now specify the preset name inside the ``<form:render>``
ViewHelper you will not see any differences yet:

.. code-block:: xml

	<form:render factoryClass="..." presetName="myCustom" />

Now we are set up to modify the custom preset, and can adjust the form output.

Adjusting a Form Element Template
---------------------------------

We can now override the fluid template path for a text field as follows:

TODO Continue here

#* Override Fluid Template Paths for a specific form element (like Textbox)

Creating a New Form Element
---------------------------

#* Using supertypes for creating new form element

Changing The Form Layout
------------------------
#* Change global partial path or global layout path
