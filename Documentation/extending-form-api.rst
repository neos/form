Extending Form API
==================

After working through this guide, you will have learned:

* how to create custom PHP based Form Element implementations
* how to create a custom Form Element renderer

Generally, this guide answers the question: **How can the form output be modified with programming?**

Custom PHP-based Form Elements
------------------------------

In the previous guides you have learned how to create custom Form Elements without writing a
single line of PHP. While this is sufficient for most cases where you mainly want to change
the visual representation or create a *specialized* version of an already existing element,
there are situations where you want to adjust the *Server-side* behavior of an element.
This is where you want to get your hands dirty and create custom Form Element implementations.
Examples for such custom Form Elements are:

* A *DatePicker* that converts the input to a ``DateTime`` object
* A *File upload* that validates and converts an uploaded file to a ``PersistentResource``
* A *Captcha* image

A Form Element must implement the ``FormElementInterface`` interface located in
``Neos.Form/Classes/Core/Model/FormElementInterface.php``.

.. tip:: Usually you want to extend the provided ``AbstractFormElement`` which already implements
   most of the methods of the interface.

Most commonly you create custom Form elements in order to preconfigure the so called ``Processing Rule``
which defines validation and property mapping instructions for an element.
Lets have a look at the ``DatePicker`` Form Element located in ``Neos.Form/Classes/FormElements/DatePicker.php``::

	class DatePicker extends \Neos\Form\Core\Model\AbstractFormElement {
	   public function initializeFormElement() {
	      $this->setDataType('DateTime');
	   }
	}

The method ``initializeFormElement()`` is called whenever a Form Element is **added to a form**.
In this example, we only set the target data type to a DateTime object. This way, property
mapping and type conversion using the registered TypeConverters is automatically triggered.

Besides being able to modify the Form Element configuration during *initialization* you can also
implement the callbacks ``beforeRendering()`` or/and ``onSubmit()`` in order to adjust the behavior
or representation of the element at *runtime*.
Lets create a new Form Element that is required only if another form field has been specified (for
example a "subscribe to newsletter" checkbox that requires you to provide an email address if checked).
For this create a new PHP class at ``Your.Package/Classes/FormElements/ConditionalRequired.php``::

	namespace Your\Package\FormElements;

	class ConditionalRequired extends \Neos\Form\Core\Model\AbstractFormElement {

	   /**
	    * Executed before the current element is outputted to the client
	    *
	    * @param \Neos\Form\Core\Runtime\FormRuntime $formRuntime
	    * @return void
	    */
	   public function beforeRendering(\Neos\Form\Core\Runtime\FormRuntime $formRuntime) {
	      $this->requireIfTriggerIsSet($formRuntime->getFormState());
	   }

	   /**
	    * Executed after the page containing the current element has been submitted
	    *
	    * @param \Neos\Form\Core\Runtime\FormRuntime $formRuntime
	    * @param mixed $elementValue raw value of the submitted element
	    * @return void
	    */
	   public function onSubmit(\Neos\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue) {
	      $this->requireIfTriggerIsSet($formRuntime->getFormState());
	   }

	   /**
	    * Adds a NotEmptyValidator to the current element if the "trigger" value is not empty.
	    * The trigger can be configured with $this->properties['triggerPropertyPath']
	    *
	    * @param \Neos\Form\Core\Runtime\FormState $formState
	    * @return void
	    */
	   protected function requireIfTriggerIsSet(\Neos\Form\Core\Runtime\FormState $formState) {
	      if (!isset($this->properties['triggerPropertyPath'])) {
	         return;
	      }
	      $triggerValue = $formState->getFormValue($this->properties['triggerPropertyPath']);
	      if ($triggerValue === NULL || $triggerValue === '') {
	         return;
	      }
	      $this->addValidator(new \Neos\Flow\Validation\Validator\NotEmptyValidator());
	   }
	}

``beforeRendering()`` is invoked just before a Form Element is actually outputted to the client.
It receives a reference to the current ``FormRuntime`` making it possible to access previously
submitted values.

``onSubmit()`` is called whenever the page containing the current Form Element is submitted. to the
server. In addition to the ``FormRuntime`` this callback also gets passed a reference to the raw value
of the submitted element value before property mapping and validation rules were applied.

In order to use the new Form Element type you first have to extend the Form Definition and specify the
``implementationClassName`` option:

.. code-block:: yaml

	Neos:
	  Form:
	    presets:
	      somePreset:
	        # ...
	        formElementTypes:
	          'Neos.FormExample:ConditionalRequired':
	            superTypes:
	              'Neos.Form:FormElement': TRUE
	            implementationClassName: 'Neos\FormExample\FormElements\ConditionalRequired'
	            renderingOptions:
	              templatePathPattern: 'resource://Neos.Form/Private/Form/SingleLineText.html'

This makes the new Form Element ``Neos.FormExample:ConditionalRequired`` available in the preset
``somePreset`` and you can use it as follows::

	$form = new FormDefinition('myForm', $formDefaults);

	$page1 = $form->createPage('page1');

	$newsletter = $page1->createElement('newsletter', 'Neos.Form:Checkbox');
	$newsletter->setLabel('Subscribe for Newsletter');

	$email = $page1->createElement('email', 'Neos.FormExample:ConditionalRequired');
	$email->setLabel('E-Mail');
	$email->setProperty('triggerPropertyPath', 'newsletter');

The line ``$email->setProperty('triggerPropertyPath', 'newsletter');`` makes the ``email`` Form Element
required depending on the value of the ``newsletter`` element.

This example is really simple but it demonstrates how you can profoundly interact with the Form handling
at every level.

Custom Form Element Renderers
-----------------------------

By default a form and all its elements are rendered with the ``FluidFormRenderer`` which is a specialized
version of the ``Fluid TemplateView``. For each renderable Form Element there exists an corresponding Fluid
template.
The template path can be changed for all or specific Form Elements as well as layout and partial paths, so
the default renderer is flexible enough to cover most scenarios. However if you want to use your own templating
engine or don't want to render HTML forms at all (think of Flash or CLI based forms) you can implement your
own Renderer and use it either for the complete form or for certain Form Elements.

As a basic example we want to implement a ``ListRenderer`` that simply outputs specified items as unordered
list. A Form Element Renderer must implement the ``RendererInterface`` interface located in
``Neos.Form/Classes/Core/Renderer/RendererInterface.php`` and usually you want to extend the provided
``AbstractRenderer`` which already implements most of the methods of the interface::

	namespace Your\Package\Renderers;

	class ListRenderer extends \Neos\Form\Core\Renderer\AbstractElementRenderer {

	   /**
	    * @param \Neos\Form\Core\Model\Renderable\RootRenderableInterface $renderable
	    * @return string
	    */
	   public function renderRenderable(\Neos\Form\Core\Model\Renderable\RootRenderableInterface $renderable) {
	      $renderable->beforeRendering($this->formRuntime);
	      $items = array();
	      if ($renderable instanceof \Neos\Form\Core\Model\FormElementInterface) {
	         $elementProperties = $renderable->getProperties();
	         if (isset($elementProperties['items'])) {
	            $items = $elementProperties['items'];
	         }
	      }
	      $content = sprintf('<h3>%s</h3>', htmlspecialchars($renderable->getLabel()));
	      $content .= '<ul>';
	      foreach ($items as $item) {
	         $content .= sprintf('<li>%s</li>', htmlspecialchars($item));
	      }
	      $content .= '</ul>';
	      $content = $this->formRuntime->invokeRenderCallbacks($content, $renderable);
	      return $content;
	   }
	}

.. note::  Don't forget to invoke ``RootRenderableInterface::beforeRendering()`` and ``FormRuntime::invokeRenderCallbacks()``
   as shown above.

.. tip:: If you write your own Renderer make sure to sanitize values with ``htmlspecialchars()`` before outputting
   them to prevent invalid HTML and XSS vulnerabilities.

Make sure to have a look at the `FusionRenderer Package<https://packagist.org/packages/neos/form-fusionrenderer>`_ that
provides Fusion based rendering for arbitrary Form Elements!