Configuring Form Builder
========================

After this guide, you will have learned how to **Configure the Form Builder through settings**

Adding a New Form Element Inside "Create Elements"
--------------------------------------------------

Let's say you have created your form element, and want to make it available inside the Form Builder. For that, you need some YAML configuration which looks as follows:

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]
	formElementTypes:
	  'Your.Package:YourFormElement':
	    # the definitions for your form element
	    formBuilder:
	      label: 'Your New Form Element'
	      group: custom
	      sorting: 200

To determine whether a form element is visible in the Form Builder, you must set ``formBuilder:group`` to a valid group. A *form element group* is used to visually group the available form elements together. In the default profile, the following groups are configured:

* input
* select
* custom
* container

The ``label`` is -- as you might expect -- the human-readable label, while the ``sorting`` determines the ordering of form elements inside their form element group.

Creating a New Form Element Group
---------------------------------

All form element groups are defined inside ``formElementGroups`` inside the preset, so that's how you can add a new group:

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]
	formElementGroups:
	  specialCustom:
	    sorting: 500
	    label: 'My special custom group'

For each group, you need to specify a human-readable ``label``, and the ``sorting`` (which determines the ordering of the groups).

Setting Default Values for Form Elements
----------------------------------------

When a form element is created, you can define some default values which are directly set on the form element. As an example, let's imagine you want to build a ``ProgrammingLanguageSelect`` where the user can choose his favorite programming language.

In this case, we want to define some default programming languages, but the integrator who builds the form should be able to add custom options as well. These default options can be set in ``Settings.yaml`` using the ``formBuilder:predefinedDefaults`` key.

Here follows the full configuration for the ``ProgrammingLanguageSelect`` (which is an example taken from the ``TYPO3.FormExample`` package):

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]
	formElementTypes:
	  'TYPO3.FormExample:ProgrammingLanguageSelect':
	    superTypes: ['TYPO3.Form:SingleSelectRadiobuttons']
	    renderingOptions:
	      templatePathPattern: 'resource://TYPO3.Form/Private/Form/SingleSelectRadiobuttons.html'

	      # here follow the form builder specific options
	      formBuilder:
	        group: custom
	        label: 'Programming Language Select'

	        # we now set some defaults which are applied once the form element is inserted to the form
	        predefinedDefaults:
	          properties:
	            options:
	              0:
	                _key: 'php'
	                _value: 'PHP'
	              1:
	                _key: 'java'
	                _value: 'Java etc'
	              2:
	                _key: 'js'
	                _value: 'JavaScript'

Contrasting Use Case: Gender Selection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Inside *Creating a new form element*, we have implemented a special *Gender Select*. Let's think a second about the differences between the *Gender Select* and the *Programming Language Select* examples:

For a *Gender* select field, the integrator using the form builder does not need to set any options for this form element, as the available choices (``Female`` and ``Male``) are predefined inside the *form element template*.

In the case of the *programming language select*, we only want to set some sensible defaults for the integrator, but want him to be able to adjust the values.

Choosing which strategy to use depends mostly on the expected usage patterns:

* In the *gender select* example, if a **new option is added to the list afterwards**, this will directly be reflected in *all forms* which use this input field.
* If you use ``predefinedDefaults``, changing these will be only applied to **new elements**, but not to already existing elements.

.. note:: In order to make the gender selection work nicely with the Form Builder,
   we should disable the ``options`` editor as follows (as the options should not be editable by the implementor):

	.. code-block:: yaml

		# we are now inside TYPO3:Form:presets:[presetName]
		formElementTypes:
		  'TYPO3.FormExample:GenderSelect':
		    formBuilder:
		      editors:
		        # Disable "options" editor
		        options: null

.. tip:: The same distinction between using ``formBuilder:predefinedDefaults`` and
   the form element type definition directly can also be used to add other elements like
   ``Validators`` or ``Finishers``.


Marking Validators and Finishers As Required
--------------------------------------------

Sometimes, you want to simplify the Form Builder User Interface and make certain options easier for your users. A frequent use-case is that you want that a certain validator, like the ``StringLength`` validator, is always shown in the user interface as it is very often used.

This can be configured as follows:


.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]
	formElementTypes:
	  'TYPO3.Form:TextMixin': # or any other type here
	    formBuilder:
	      editors:
	        validation:
	          availableValidators:
	            'TYPO3.Flow:StringLength': # or any other validator
	              # mark this validator required such that it is always shown.
	              required: true

Finishers
~~~~~~~~~

The same works for Finishers, for example the following configuration makes the EmailFinisher mandatory:

.. code-block:: yaml

	# we are now inside TYPO3:Form:presets:[presetName]
	formElementTypes:
	  'TYPO3.Form:Form':
	    formBuilder:
	      editors:
	        finishers:
	          availableFinishers:
	            'TYPO3.Form:Email': # or any other finisher
	              # mark this finisher required such that it is always shown.
	              required: true


Finishing Up
------------

You should now have some receipes at hand on how to modify the Form Builder. Read the next chapter for some more advanced help.