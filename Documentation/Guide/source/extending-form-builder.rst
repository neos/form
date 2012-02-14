
Extending Form Builder
======================

After working through this guide, you will have learned:

* How to include custom CSS into the form builder
* How to write a custom finisher editor

* **How can the form builder be adjusted**

An in-depth reference on how to extend the form builder using custom JavaScript can be found in the start page of the Form Builder
API documentation.


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

.. tip:: Creating a custom *validator editor* works in the same way.


.. todo: create custom editor?