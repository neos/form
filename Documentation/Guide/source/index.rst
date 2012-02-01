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

Anatomy of a Form
-----------------

In the TYPO3.Form package a form is described by the `Form Definition` that consists of one or more `Pages` in which the actual `Form Elements` are located.
So the structure of a basic contact form could look like this:

* Contact Form *(Form)*
    * Page 01 *(Page)*
        * Name *(Single-line Text)*
        * Email *(Single-line Text)*
        * Message *(Multi-line Text)*

* TODO: Simplified Class Diagramm

* TODO: Short intro to FormDefinition, Page & FormElement` A **page** has an (optional) label and is the container for one or more Form Elements. A form consists of at least one page even if you don't need multiple steps.

.. note:: There are special *Section* Form Elements that can contain other elements themselves.

Create your first form
----------------------

Now, let's try to create the basic contact form from above.
For this we need a so called `FormFactory`. That is a class that implements the `\TYPO3\Form\Factory\FormFactoryInterface`. You can extend the `AbstractFormFactory` which already provides some helpful methods:

::

	namespace Your\Package;

	use TYPO3\FLOW3\Annotations as FLOW3;
	use TYPO3\Form\Core\Model\FormDefinition;

	class QuickstartFactory extends \TYPO3\Form\Factory\AbstractFormFactory {

		/**
		 * @param array $factorySpecificConfiguration
		 * @param $presetName
		 * @return \TYPO3\Form\Core\Model\FormDefinition
		 */
		public function build(array $factorySpecificConfiguration, $presetName) {
		}
	}

As you can see there is one method that you have to implement. `build()` expects two parameters that we can ignore for now. In that method you create the `FormDefinition` and return it.
Lets add the one page and input fields for *name*, *email* and *message* of our contact form:

::

	public function build(array $factorySpecificConfiguration, $presetName) {
		$formConfiguration = $this->getPresetConfiguration($presetName);
		$form = new FormDefinition('ContactForm', $formConfiguration);

		$page1 = $form->createPage('page1');

		$name = $page1->createElement('name', 'TYPO3.Form:SingleLineText');
		$name->setLabel('Name');

		$email = $page1->createElement('email', 'TYPO3.Form:SingleLineText');
		$email->setLabel('Email');

		$comments = $page1->createElement('message', 'TYPO3.Form:MultiLineText');
		$comments->setLabel('Message');

		return $form;
	}

Render a form
-------------

Now that we have created the first FormDefinition how can we display the actual form?
That is really easy with the provided ViewHelper:

::

	{namespace form=TYPO3\Form\ViewHelpers}
	<form:render factoryClass="Your\Package\YourFactory" />

If you put that snippet in your Fluid template and replace `Your\Package` with your package namespace and `YourFactory` with the class name of the previously generated form factory, you should see a form consisting of the three text fields and a submit button.
But as you can see, none of the fields are required and the email address is not verified. So let's add some basic validation rules:

Validation
----------

Every FormElement implements the FormElementInterface which provides a convenient way to work with FLOW3 validators:

::

	$name->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());

	$email->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());
	$email->addValidator(new \TYPO3\FLOW3\Validation\Validator\EmailAddressValidator());

	$comments->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());
	$comments->addValidator(new \TYPO3\FLOW3\Validation\Validator\StringLengthValidator(array('minimum' => 3)));

With the `addValidator` method you can attach one or more validators to a FormElement.
If you save the changes and reload the page you can see that all text fields are required now, that the email address is verified and that you need to write a message of at least 3 characters. If you try to submit the form with invalid data, validation errors are displayed next to each erroneous field.

If you do enter name, a valid email address and a message you can submit the form - and see a blank page. That's where so called `Finishers` come into play.

Finishers
---------

A `Finisher` is a piece of PHP code that is executed as soon as a form has been successfully submitted (if the last page has been sent off and no validation errors occurred).
You can attach multiple finishers to a form.
For this example we might want to send the data to an email address:

::

	$emailFinisher = new \TYPO3\Form\Finishers\EmailFinisher();
	$emailFinisher->setOptions(array(
		'templatePathAndFilename' => 'resource://Your.Package/Private/Templates/ContactForm/NotificationEmail.txt',
		'recipientAddress' => 'your@example.com',
		'senderAddress' => 'mailer@example.com',
		'replyToAddress' => '{email}',
		'subject' => 'Contact Request',
		'format' => \TYPO3\Form\Finishers\EmailFinisher::FORMAT_PLAINTEXT
	));
	$form->addFinisher($emailFinisher);

And afterwards we want to redirect the user to some confirmation action:

::

	$redirectFinisher = new \TYPO3\Form\Finishers\RedirectFinisher();
	$redirectFinisher->setOptions(
		array('action' => 'confirmation')
	);
	$form->addFinisher($redirectFinisher);


That's it for the quickstart. The complete code of your form factory should look something like this now:

::

	namespace Your\Package;

	use TYPO3\FLOW3\Annotations as FLOW3;
	use TYPO3\Form\Core\Model\FormDefinition;

	/**
	 * FLOW3\Scope("singleton")
	 */
	class QuickstartFactory extends \TYPO3\Form\Factory\AbstractFormFactory {

		/**
		 * @param array $factorySpecificConfiguration
		 * @param $presetName
		 * @return \TYPO3\Form\Core\Model\FormDefinition
		 */
		public function build(array $factorySpecificConfiguration, $presetName) {
			$formConfiguration = $this->getPresetConfiguration($presetName);
			$form = new FormDefinition('ContactForm', $formConfiguration);

			$page1 = $form->createPage('page1');

			$name = $page1->createElement('name', 'TYPO3.Form:SingleLineText');
			$name->setLabel('Name');
			$name->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());

			$email = $page1->createElement('email', 'TYPO3.Form:SingleLineText');
			$email->setLabel('Email');
			$email->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());
			$email->addValidator(new \TYPO3\FLOW3\Validation\Validator\EmailAddressValidator());

			$comments = $page1->createElement('message', 'TYPO3.Form:MultiLineText');
			$comments->setLabel('Message');
			$comments->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());
			$comments->addValidator(new \TYPO3\FLOW3\Validation\Validator\StringLengthValidator(array('minimum' => 3)));

			$emailFinisher = new \TYPO3\Form\Finishers\EmailFinisher();
			$emailFinisher->setOptions(array(
				'templatePathAndFilename' => 'resource://Your.Package/Private/Templates/ContactForm/NotificationEmail.txt',
				'recipientAddress' => 'your@example.com',
				'senderAddress' => 'mailer@example.com',
				'replyToAddress' => '{email}',
				'subject' => 'Contact Request',
				'format' => \TYPO3\Form\Finishers\EmailFinisher::FORMAT_PLAINTEXT
			));
			$form->addFinisher($emailFinisher);

			$redirectFinisher = new \TYPO3\Form\Finishers\RedirectFinisher();
			$redirectFinisher->setOptions(
				array('action' => 'confirmation')
			);
			$form->addFinisher($redirectFinisher);

			return $form;
		}
	}




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

