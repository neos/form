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

In the *Quickstart* guide, you have seen how a basic form can be built. We
will now dissect the form element creation a little more, and explain the lines
which you might not have understood yet.

Let's have a look at the boilerplate code inside the form factory again::

	public function build(array $factorySpecificConfiguration, $presetName) {
		$formConfiguration = $this->getPresetConfiguration($presetName);
		$form = new FormDefinition('contactForm', $formConfiguration);
		// ...
	}

You see that the second parameter is a ``$presetName`` which is passed to
``getPresetConfiguration()``. So, let's introduce the concept of *presets* now.

A **Preset** is a container for pre-defined form configuration, and is the basic
way to adjust the form's output. Presets are defined inside the ``Settings.yaml``
file, like in the following example:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	      preset1:
	        title: 'My First Preset'
	        formElementTypes:
	          'Neos.Form:SingleLineTextfield':
	            # configuration for the single line textfield
	      preset2:
	        title: 'My Second Preset'
	        parentPreset: 'preset1'
	        # because preset2 *inherits* from preset1, only the changes between
	        # preset1 and preset2 need to be defined here.

The above example defines two presets (``preset1`` and ``preset2``). Because
``preset2`` defines a ``parentPreset``, it **inherits** all options from ``preset1``
if not specified otherwise.

.. tip:: The Neos.Form package already defines a preset with the name ``default``
   which contains all standard form elements. Look into ``Neos.Form/Configuration/Settings.yaml``
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

	$name = $page1->createElement('name', 'Neos.Form:SingleLineText');
	$name->setLabel('Name');

In the above example, the form element type is ``Neos.Form:SingleLineText``, and
when creating the form element, it *applies all default values* being set inside
the form element type. As an example, take the following type definition:

.. code-block:: yaml

	'Neos.Form:SingleLineText':
	  defaultValue: 'My Default Text'
	  properties:
	    placeholder: 'My Placeholder Text'

That's exactly the same as if one wrote the following PHP code::

	$name->setDefaultValue('My Default Text');
	$name->setProperty('placeholder', 'My Placeholder Text');

So ``$page->createElement($identifier, $formElementType)`` is essentially a very
specialized *factory method*, which automatically applies the default values from
the *form element definition* on the newly created form object before returning it.

.. tip:: The defaults are not only applied on single form elements, but also
   on the FormDefinition and Page objects. The FormDefinition object has, by
   convention, the *form element type* ``Neos.Form:Form``, but you can also
   override it by passing the to-be-used type as third parameter to the
   constructor of :api-core-model:`FormDefinition`.

   A *page* has, by default, the *form element type* ``Neos.Form:Page``, and you can
   override it by supplying a second parameter to the ``createPage()`` method of
   :api-core-model:`FormDefinition`.

Supertypes
----------

Now, there's one more secret ingredient which makes the form framework powerful:
Every form element type can have one or multiple **supertypes**; and this
allows to only specify the differences between the "parent" form element and
the newly created one, effectively creating an inheritance hierarchy of form elements.

The following example demonstrates this:

.. code-block:: yaml


	'Neos.Form:SingleLineText':
	  defaultValue: 'My Default Text'
	  properties:
	    placeholder: 'My Placeholder Text'
	'Neos.Form:SpecialText':
	  superTypes:
	    'Neos.Form:SingleLineText' : TRUE
	  defaultValue: 'My special text'

Here, the ``SpecialText`` inherits the ``placeholder`` property from the ``SingleLineText``
and only overrides the default value.

Together, presets (with parent presets) and form element types (with supertypes)
form a very flexible foundation to customize the rendering in any imaginable way,
as we will explore in the remainder of this guide.

.. note:: If multiple super types are specified, they are evaluated from *left to right*, i.e.
   later super types override previous definitions.


Previously the superTypes configuration was just a simple list of strings:

.. code-block:: yaml

	'Neos.Form:SpecialText':
	  superTypes:
	    'Neos.Form:SingleLineText': TRUE
	  defaultValue: 'My special text'

But this made it impossible to *unset* a super type from a 3rd party package.
The old syntax is still supported but is deprecated and might be removed in future versions.

Creating a Custom Preset
------------------------

First, we create a sub-preset inheriting from the ``default`` preset. For that,
open up ``Your.Package/Configuration/Settings.yaml`` and insert the following
contents:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	      myCustom:
	        title: 'Custom Elements'
	        parentPreset: 'default'

You now created a sub preset named ``myCustom`` which behaves exactly the same as
the default preset. If you now specify the preset name inside the ``<form:render>``
ViewHelper you will not see any differences yet:

.. code-block:: xml

	<form:render factoryClass="..." presetName="myCustom" />

Now we are set up to modify the custom preset, and can adjust the form output.

Adjusting a Form Element Template
---------------------------------

The templates of the default Form Elements are located in ``Neos.Form/Resources/Private/Form/``.
They are standard Fluid templates and most of them are really simple. Open up the
``Single-Line Text`` template for example:

.. code-block:: xml

	<f:layout name="Neos.Form:Field" />
	<f:section name="field">
	   <f:form.textfield property="{element.identifier}" id="{element.uniqueIdentifier}"
	                     placeholder="{element.properties.placeholder}" errorClass="error" />
	</f:section>

As you can see, the Form Element templates use layouts in order to reduce duplicated markup.

.. tip:: The Fluid Form Renderer expects layout and partial names in the format ``<PackageKey>:<Name>``.
   That makes it possible to reference layouts and partials from other packages!

We'll see how to change the layout in the next section. For now let's try to simply change the
``class`` attribute of the *SingleLineText* element.

For that, copy the default template to ``Your.Package/Private/Resources/CustomElements/SingleLineText.html``
and adjust it as follows:

.. code-block:: xml

	<f:layout name="Neos.Form:Field" />
	<f:section name="field">
	   <f:form.textfield property="{element.identifier}" id="{element.uniqueIdentifier}"
	                     placeholder="{element.properties.placeholder}" errorClass="error"
                           class="customClass" />
	</f:section>


Now, you only need to tell the framework to use your newly created template instead of the default one.
This can be archieved by overriding the rendering option ``templatePathPattern`` in the *form element
type definition*.

Adjust ``Your.Package/Configuration/Settings.yaml`` accordingly:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	      myCustom:
	        title: 'Custom Elements'
	        parentPreset: 'default'
	        formElementTypes:
	          'Neos.Form:SingleLineText':
	            renderingOptions:
	              templatePathPattern: 'resource://Your.Package/Private/CustomElements/SingleLineText.html'

Now, all ``Single-Line Text`` elements will have a class attribute of ``customClass``
when using the ``myCustom`` preset.

A more realistic use-case would be to change the arrangement of form elements. Read on to see how you can easily change the
layout of a form.

Changing The Form Layout
------------------------

By default, validation errors are rendered next to each form element. Imagine you want to render validation errors of the
current page *above* the form instead. For this you need to adjust the previously mentioned **field layout**.

The provided default field layout located in ``Neos.Form/Resources/Private/Form/Layouts/Field.html`` is a bit more verbose
as it renders the label, validation errors and an asterisk if the element is required (we slightly reformatted the template
here to improve readability):

.. code-block:: xml

	{namespace form=Neos\Form\ViewHelpers}
	<f:validation.results for="{element.identifier}">
	   <!-- wrapping div for the form element; contains an identifier for the form element if we are
              in preview mode -->
	   <div class="clearfix{f:if(condition: validationResults.flattenedErrors, then: ' error')}"
	        <f:if condition="{element.rootForm.renderingOptions.previewMode}">
	           data-element="{form:form.formElementRootlinePath(renderable:element)}"
	        </f:if>
	   >
	      <!-- Label for the form element, and required indicator -->
	      <label for="{element.uniqueIdentifier}">{element.label -> f:format.nl2br()}
	         <f:if condition="{element.required}">
	            <f:render partial="Neos.Form:Field/Required" />
	         </f:if>
	       </label>

	      <!-- the actual form element -->
	      <div class="input">
	         <f:render section="field" />

	         <!-- validation errors -->
	         <f:if condition="{validationResults.flattenedErrors}">
	            <span class="help-inline">
	               <f:for each="{validationResults.errors}" as="error">
	                  {error -> f:translate(id: error.code, arguments: error.arguments,
	                                        package: 'Neos.Form', source: 'ValidationErrors')}
	                  <br />
	               </f:for>
	            </span>
	         </f:if>
	      </div>
	   </div>
	</f:validation.results>

Copy the layout file to ``Your.Package/Private/Resources/CustomElements/Layouts/Field.html`` and remove the validation related lines:

.. code-block:: xml

	{namespace form=Neos\Form\ViewHelpers}
	<f:validation.results for="{element.identifier}">
	   <!-- wrapping div for the form element; contains an identifier for the form element if we are
              in preview mode -->
	   <div class="clearfix{f:if(condition: validationResults.flattenedErrors, then: ' error')}"
	        <f:if condition="{element.rootForm.renderingOptions.previewMode}">
	           data-element="{form:form.formElementRootlinePath(renderable:element)}"
	        </f:if>
	   >
	      <!-- Label for the form element, and required indicator -->
	      <label for="{element.uniqueIdentifier}">{element.label -> f:format.nl2br()}
	         <f:if condition="{element.required}">
	            <f:render partial="Neos.Form:Field/Required" />
	         </f:if>
	       </label>

	      <!-- the actual form element -->
	      <div class="input">
	         <f:render section="field" />
	      </div>
	   </div>
	</f:validation.results>

Additionally you need to adjust the default form template located in ``Neos.Form/Resources/Private/Form/Form.html`` (remember
that a :api-core-model:`FormDefinition` also has a form element type, by default of ``Neos.Form:Form``), which looks
as follows by default:

.. code-block:: xml

	{namespace form=Neos\Form\ViewHelpers}
	<form:form object="{form}" action="index" method="post" id="{form.identifier}"
	           enctype="multipart/form-data">
	   <form:renderRenderable renderable="{form.currentPage}" />
	   <div class="actions">
	      <f:render partial="Neos.Form:Form/Navigation" arguments="{form: form}" />
	   </div>
	</form:form>

Copy this template file to ``Your.Package/Private/Resources/CustomElements/Form.html`` and add the validation result
rendering:

.. code-block:: xml

	{namespace form=Neos\Form\ViewHelpers}
	<form:form object="{form}" action="index" method="post" id="{form.identifier}"
	           enctype="multipart/form-data">
	   <f:validation.results>
	      <f:if condition="{validationResults.flattenedErrors}">
	         <ul class="error">
	            <f:for each="{validationResults.flattenedErrors}" as="elementErrors"
	                   key="elementIdentifier" reverse="true">
	               <li>
	                  {elementIdentifier}:
	                  <ul>
	                     <f:for each="{elementErrors}" as="error">
	                        <li>{error}</li>
	                     </f:for>
	                  </ul>
	               </li>
	            </f:for>
	         </ul>
	      </f:if>
	   </f:validation.results>
	   <form:renderRenderable renderable="{form.currentPage}" />
	   <div class="actions">
	      <f:render partial="Neos.Form:Form/Navigation" arguments="{form: form}" />
	   </div>
	</form:form>

Now, you only need to adjust the form definition in order to use the new templates:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	      ########### CUSTOM PRESETS ###########

	      myCustom:
	        title: 'Custom Elements'
	        parentPreset: 'default'
	        formElementTypes:

	           # ...

	           ### override template path of Neos.Form:Form ###
	          'Neos.Form:Form':
	            renderingOptions:
	              templatePathPattern: 'resource://Neos.FormExample/Private/CustomElements/Form.html'

	           ### override default layout path ###
	          'Neos.Form:Base':
	            renderingOptions:
	              layoutPathPattern: 'resource://Neos.FormExample/Private/CustomElements/Layouts/{@type}.html'

.. tip:: You can use **placeholders** in ``templatePathPattern``, ``partialPathPattern`` and ``layoutPathPattern``:
   ``{@package}`` will be replaced by the package key and ``{@type}`` by the current form element type
   without namespace. A small example shall illustrate this:

   If the form element type is ``Your.Package:FooBar``, then ``{@package}`` is replaced by ``Your.Package``,
   and ``{@type}`` is replaced by ``FooBar``. As partials and layouts inside form elements are also specified
   using the ``Package:Type`` notation, this replacement also works for partials and layouts.

.. _creating-a-new-form-element:

Creating a New Form Element
---------------------------

With the Form Framework it is really easy to create additional Form Element types.
Lets say you want to create a specialized version of the ``Neos.Form:SingleSelectRadiobuttons`` that already provides
two radio buttons for ``Female`` and ``Male``. That's just a matter of a few lines of yaml:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	       ########### CUSTOM PRESETS ###########

	      myCustom:
	        title: 'Custom Elements'
	        parentPreset: 'default'
	        formElementTypes:

	           # ...

	          'Your.Package:GenderSelect':
	            superTypes:
	              'Neos.Form:SingleSelectRadiobuttons': TRUE
	            renderingOptions:
	              templatePathPattern: 'resource://Neos.Form/Private/Form/SingleSelectRadiobuttons.html'
	            properties:
	              options:
	                f: 'Female'
	                m: 'Male'

As you can see, you can easily extend existing Form Element Definitions by specifying the ``superTypes``.

.. tip:: We have to specify the ``templatePathPattern`` because according to the default path pattern
   the template would be expected at ``Your.Package/Private/Resources/Form/GenderSelect.html`` otherwise.

.. note:: Form Elements will only be available in the preset they're defined (and in it's sub-presets).
   Therefore you should consider adding Form Elements in the ``default`` preset to make them available for all
   Form Definitions extending the default preset.


