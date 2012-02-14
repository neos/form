
Extending Form Builder
======================

After working through this guide, you will have learned:

* How to include custom CSS into the form builder
* How to write a custom finisher editor

* **How can the form builder be adjusted**

An in-depth reference on how to extend the form builder using custom JavaScript can be found in the start page of the Form Builder
API documentation.

.. _adjusting-form-builder-with-custom-css:

Adjusting the Form Builder with Custom CSS
------------------------------------------

Let's say you want to adjust the form builder with a custom CSS file inside ``Your.Package/Resources/Public/FormBuilderAdjustments.css``. Then, you need to tell the form builder to load this additional stylesheet as well. You can do that using an entry inside ``Settings.yaml`` of your package which looks as follows:

.. code-block:: yaml

	TYPO3:
	  FormBuilder:
	    stylesheets:
	      customAdjustments:
	        files: ['resource://Your.Package/Public/FormBuilderAdjustments.css']
	        sorting: 200

Most important is the ``sorting`` property, as it defines the *order in which the CSS files are included*. Every sorting up to 100 is reserved for internal use by the form builder, so you should use sorting numbers above 100 unless you have a good reason to do otherwise.

.. tip:: Loading additional JavaScript files into the form builder works in the same manner.


.. _overriding-form-builder-handlebars-template:

Overriding Form Builder Handlebars Template
-------------------------------------------

Let's say we want to adjust the header of the form builder, such that it displays your company up there as well. For that, we need to modify the default *handlebars template* for the header area.

.. warning:: If you modify handlebars templates, you might need to adjust them after a new version of the form builder
   has been released! Modification of handlebars templates is useful for **unplanned extensibility**, but you should only
   do it as last resort!

The default template is located inside ``TYPO3.FormBuilder/Resources/Private/FormBuilderTemplates/Header.html`` and looks as follows:

.. code-block:: html

	<h1>Form Builder - {{TYPO3.FormBuilder.Model.Form.formDefinition.label}}</h1>
	{{#if TYPO3.FormBuilder.Model.Form.currentlyLoadingPreview}}
	   <span id="typo3-formbuilder-loading">Loading..</span>
	{{/if}}

	<ul id="typo3-formbuilder-toolbar">
	   <li class="typo3-formbuilder-preset">
	      {{view TYPO3.FormBuilder.View.Header.PresetSelector}}
	   </li>
	   <li class="typo3-formbuilder-preview">
	      {{#view TYPO3.FormBuilder.View.Header.PreviewButton class="typo3-formbuilder-button icon"}}Preview{{/view}}
	   </li>
	   <li class="typo3-formbuilder-save">
		{{#view TYPO3.FormBuilder.View.Header.SaveButton class="typo3-formbuilder-button icon"}}Save{{/view}}
	   </li>
	</ul>

We can just copy it to ``Your.Package/Resources/Private/FormBuilderTemplates/Header.html`` and adjust it as needed, modifying the part inside the ``<h1>...</h1>`` to:

.. code-block:: html

	<h1>Your Company Form Builder - {{TYPO3.FormBuilder.Model.Form.formDefinition.label}}</h1>

Then, we need to tell the form builder that we want to use a different handlebars template for the header. For that, we need the following ``Settings.yaml``:

.. code-block:: yaml

	TYPO3:
	  FormBuilder:
	    handlebarsTemplates:
	      Header: resource://Your.Package/Private/FormBuilderTemplates/Header.html

.. warning:: Make sure that your package is loaded **after the FormBuilder package** if you want to override such settings.

Creating a Custom Editor
------------------------

Every form element is edited on the right side of the Form Builder in the *element options panel*. In order to be flexible and extensible, the element options panel is a container for **editors** which, as a whole, edit the form element. There are a multitude of predefined editors, ranging from a simple text input field up to a grid widget for editing properties.

All editors for a given form element are defined inside the ``formElementTypes`` definition, looking as follows:

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]:formElementTypes
	'TYPO3.Form:TextMixin':
	  formBuilder:
	    editors:
	      placeholder: # an arbitrary key for identifying the editor instance
	        sorting: 200 # the sorting determines the ordering of the different editors inside the element options panel
	        viewName: 'JavaScript.View.Class.Name' # the JavaScript view class name which should be used here
	        # additionally, you can define view-specific options here
            # here, you can define some more editors.

We will now create a custom editor for rendering a *select* box, and will add it to the *File Upload* form element such that a user can choose the file types he allows. The finished editor is part of the standard FormBuilder distribution inside ``TYPO3.FormBuilder/Resources/Private/CoffeeScript/elementOptionsPanelEditors/basic.coffee``.

.. note:: If you want to create your completely own editor, you need to include the additional JavaScript file. How this is done is explained in detail inside :ref:`adjusting-form-builder-with-custom-css`

The Basic Setup
~~~~~~~~~~~~~~~

.. note:: We'll develop the editor in `CoffeeScript <http://coffeescript.org>`_, but you are of course free to also use JavaScript.

We will extend our editor from ``TYPO3.FormBuilder.View.ElementOptionsPanel.Editor.AbstractPropertyEditor``:

.. code-block:: coffeescript

	TYPO3.FormBuilder.View.ElementOptionsPanel.Editor.SelectEditor = AbstractPropertyEditor.extend {
	   templateName: 'ElementOptionsPanel-SelectEditor'
	}

Then, we will create a basic handlebars template and register it underneath ``ElementOptionsPanel-SelectEditor`` (as described in :ref:`overriding-form-builder-handlebars-template`). We'll just copy over an existing editor template and slightly adjust it:

.. code-block:: html

	<div class="typo3-formbuilder-controlGroup">
	   <label>{{label}}:</label>
	   <div class="typo3-formbuilder-controls">
	      [select should come here]
	   </div>
	</div>

.. note:: Don't forget to register the handlebars template ``ElementOptionsPanel-SelectEditor`` inside your ``Settings.yaml``.

Now that we have all the pieces ready, let's actually use the editor inside the ``TYPO3.Form:FileUpload`` form element:

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]:formElementTypes
	'TYPO3.Form:FileUpload':
         formBuilder:
           editors:
	       allowedExtensions:
	         sorting: 200
	         viewName: 'TYPO3.FormBuilder.View.ElementOptionsPanel.Editor.SelectEditor'

After reloading the form builder, you will see that the file upload field has a field: ``[select should come here]`` displayed inside the element options panel.

Now that we have the basics set up, let's fill the editor with life by actually implementing it.

Implementing the Editor
~~~~~~~~~~~~~~~~~~~~~~~

Everything inside here is just JavaScript development with EmberJS, using bindings and computed properties. If that sound like chinese to you, head over to the `EmberJS <http://emberjs.com>`_ website and read it up.

We somehow need to configure the available options inside the editor, and come up with the following YAML on how we want to configure the file types:

.. code-block:: yaml

	allowedExtensions:
	  sorting: 200
	  label: 'Allowed File Types'
	  propertyPath: 'properties.allowedExtensions'
	  viewName: 'TYPO3.FormBuilder.View.ElementOptionsPanel.Editor.SelectEditor'
	  availableElements:
	    0:
	      value: ['doc', 'docx', 'odt', 'pdf']
	      label: 'Documents (doc, docx, odt, pdf)'
	    1:
	      value: ['xls']
	      label: 'Spreadsheet documents (xls)'

Furthermore, the above example sets the ``label`` and ``propertyPath`` options of the element editor. The ``label`` is shown in front of the element, and the ``propertyPath`` points to the form element option which shall be modified using this editor.

All properties of such an editor definition are made available inside the editor object itself, i.e. the ``SelectEditor`` now magically has an ``availableElements`` property which we can use inside the Handlebars template to bind the select box options to. Thus, we remove the ``[select should come here]`` and replace it with ``Ember.Select``:

.. code-block:: html

	{{view Ember.Select contentBinding="availableElements" optionLabelPath="content.label"}}

Now, if we reload, we already see the list of choices being available as a dropdown.

Saving the Selection
~~~~~~~~~~~~~~~~~~~~

Now, we only need to save the selection inside the model again. For that, we bind the current selection to a property in our view using the ``selectionBinding`` of the ``Ember.Select`` view:

.. code-block:: html

	{{view Ember.Select contentBinding="availableElements" optionLabelPath="content.label" selectionBinding="selectedValue"}}

Then, let's create a *computed property* ``selectedValue`` inside the editor implementation, which updates the ``value`` property and triggers the change notification callback ``@valueChanged()``:

.. code-block:: coffeescript

	SelectEditor = AbstractPropertyEditor.extend {
	   templateName: 'ElementOptionsPanel-SelectEditor'
	   # API: list of available elements to be shown in the select box; each element should have a "label" and a "value".
	   availableElements: null

	   selectedValue: ((k, v) ->
	      if arguments.length >= 2
	         # we need to set the value
	         @set('value', v.value)
	         @valueChanged()

	      # get the current value
	      for element in @get('availableElements')
	         return element if element.value == @get('value')

	      # fallback if value not found
	      return null
	   ).property('availableElements', 'value').cacheable()
	}

That's it :)


Creating a Finisher Editor
--------------------------

Let's say we have implemented an *DatabaseFinisher* which has some configuration options like the table name, and you want to make these configuration options editable inside the Form Builder. This can be done using a custom handlebars template, and some configuration. In many cases, you do not need to write any JavaScript for that.

You need to do three things:

1. Register the finisher as a *Finisher Preset*
2. Configure the finisher editor for the form to include the newly created finisher as available finisher
3. create and include the handlebars template

.. code-block:: yaml

	TYPO3:
	  Form:
	    presets:
	      yourPresetName: # fill in your preset name here, or "default"
	        # 1. Register your finisher as finisher preset
	        finisherPresets:
	          'Your.Package:DatabaseFinisher':
	             implementationClassName: 'Your\Package\Finishers\DatabaseFinisher'
	        formElementTypes:
	          'TYPO3.Form:Form':
	            formBuilder:
	              editors:
	                finishers:
	                  availableFinishers:
	                    # Configure the finisher editor for the form to include
	                    # the newly created finisher as available finisher
	                    'Your.Package:DatabaseFinisher':
	                      label: 'Database Persistence Finisher'
	                      templateName: 'Finisher-YourPackage-DatabaseFinisher'
	  FormBuilder:
	    handlebarsTemplates:
	      # include the handlebars template
	      Finisher-YourPackage-DatabaseFinisher: resource://Your.Package/Private/FormBuilderTemplates/DatabaseFinisher.html

Now, you only need to include the appropriate Handlebars template, which could look as follows:

.. code-block:: html

	<h4>
	   {{label}}
	   {{#view Ember.Button target="parentView" action="remove"
	                        isVisibleBinding="notRequired"
	                        class="typo3-formbuilder-removeButton"}}Remove{{/view}}
	</h4>

	<div class="typo3-formbuilder-controlGroup">
	   <label>Database Table</label>
	   <div class="typo3-formbuilder-controls">
	      {{view Ember.TextField valueBinding="currentCollectionElement.options.databaseTable"}}
	   </div>
	</div>

.. tip:: Creating a custom *validator editor* works in the same way, just that they have to be registered
   underneath ``validatorPresets`` and the editor is called ``validators`` instead of ``finishers``.

