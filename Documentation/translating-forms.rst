.. _translating-forms:

Translating Forms
=================

If a form has been set up, all elements will use the labels, placeholders and so forth as configured.
To have the form translated depending on the current locale, you need to configure a package to load
the translations from and add the translations as XLIFF files.

Configuration
-------------

The package to load the translations from is configured in the form preset being used. The simplest
way to configure it is this:

.. code-block:: yaml

    TYPO3:
      Form:
        presets:
          default:
            formElementTypes:
              'TYPO3.Form:Base':
                renderingOptions:
                  translationPackage: 'AcmeCom.SomePackage'

Of course it can be set in a custom preset in the same way.

The translation of validation error messages uses the TYPO3.Flow package by default, to avoid having to
copy the validation errors message catalog to all packages used for form translation. If you want to
adjust those error messages as well, copy ``ValidationErrors.xlf`` to your package and set the option
``validationErrorTranslationPackage`` to your package key.

XLIFF files
-----------

The XLIFF files follow the usual rules, the ``Main`` catalog is used. The Form package comes with the following
catalog (``Main.xlf``):

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
        <file original="" product-name="TYPO3.Form" source-language="en" datatype="plaintext">
            <body>
                <trans-unit id="forms.navigation.previousPage" xml:space="preserve">
                    <source>Previous page</source>
                </trans-unit>
                <trans-unit id="forms.navigation.nextPage" xml:space="preserve">
                    <source>Next page</source>
                </trans-unit>
                <trans-unit id="forms.navigation.submit" xml:space="preserve">
                    <source>Submit</source>
                </trans-unit>
            </body>
        </file>
    </xliff>

It should be copied to make sure the three expected units are available and can then be amended by your own
units.

For most reliable translations, the units should be given id properties based on the form configuration.
The schema is as follows:

forms.navigation.nextPage
  In multi-page forms this is used for the navigation.
forms.navigation.previousPage
  In multi-page forms this is used for the navigation.
forms.navigation.submitButton
  In forms this is used for the submit button.

Forms and sections can have their labels translated using this, where where ``{identifier}`` is the identifier
of the page or section itself:

forms.pages.{identifier}.label
  The label used for a form page.
forms.sections.{identifier}.label
  The label used for a form section.

The actual elements of a form have their id constructed by appending one of the following to
``forms.elements.{identifier}.``, where ``{identifier}`` is the identifier of the form element
itself:

label
  The label for an element.
placeholder
  The placeholder for an element, if applicable.
description
  dkjsadhsajk
text
  The text of a ``StaticText`` element.
confirmationLabel
  Used in the ``PasswordWithConfirmation`` element.
passwordDescription
  Used in the ``PasswordWithConfirmation`` element.

The labels of radio buttons and select field options can be translated using the following schema,
where ``{identifier}`` is the identifier of the form element itself and ``value`` is the value assigned
to the option:

forms.elements.{identifier}.options.{value}
  Used to translate labels of radio buttons and select field entries.

Complete example
----------------

This is the example form used elsewhere in this documentation:

* Contact Form *(Form)*
    * Page 01 *(Page)*
        * Name *(Single-line Text)*
        * Email *(Single-line Text)*
        * Message *(Multi-line Text)*

Assume it is configured like this using YAML:

.. code-block:: yaml

    type: 'TYPO3.Form:Form'
    identifier: 'contact'
    label: 'Contact form'
    renderables:
      -
        type: 'TYPO3.Form:Page'
        identifier: 'page-one'
        renderables:
          -
            type: 'TYPO3.Form:SingleLineText'
            identifier: name
            label: 'Name'
            validators:
              - identifier: 'TYPO3.Flow:NotEmpty'
            properties:
              placeholder: 'Please enter your full name'
          -
            type: 'TYPO3.Form:SingleLineText'
            identifier: email
            label: 'Email'
            validators:
              - identifier: 'TYPO3.Flow:NotEmpty'
              - identifier: 'TYPO3.Flow:EmailAddress'
            properties:
              placeholder: 'Enter a valid email address'
          -
            type: 'TYPO3.Form:MultiLineText'
            identifier: message
            label: 'Message'
            validators:
              - identifier: 'TYPO3.Flow:NotEmpty'
            properties:
              placeholder: 'Enter your message here'

.. note:: You may leave out ``label`` and ``placeholder`` if you use id-based matching for the translation.
   Be aware though, that you will get empty labels and placeholders in case the translation fails or is not
   available.

The following XLIFF would allow to translate the form:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
        <file original="" product-name="TYPO3.Form" source-language="en" datatype="plaintext">
            <body>
                <trans-unit id="forms.navigation.previousPage" xml:space="preserve">
                    <source>Previous page</source>
                </trans-unit>
                <trans-unit id="forms.navigation.nextPage" xml:space="preserve">
                    <source>Next page</source>
                </trans-unit>
                <trans-unit id="forms.navigation.submit" xml:space="preserve">
                    <source>Submit</source>
                </trans-unit>

                <trans-unit id="forms.pages.page-one" xml:space="preserve">
                    <source>Submit</source>
                </trans-unit>

                <trans-unit id="forms.elements.name.label" xml:space="preserve">
                    <source>Name</source>
                </trans-unit>
                <trans-unit id="forms.elements.name.placeholder" xml:space="preserve">
                    <source>Please enter your full name</source>
                </trans-unit>

                <trans-unit id="forms.elements.email.label" xml:space="preserve">
                    <source>Email</source>
                </trans-unit>
                <trans-unit id="forms.elements.email.placeholder" xml:space="preserve">
                    <source>Enter a valid email address</source>
                </trans-unit>

                <trans-unit id="forms.elements.message.label" xml:space="preserve">
                    <source>Message</source>
                </trans-unit>
                <trans-unit id="forms.elements.message.placeholder" xml:space="preserve">
                    <source>Enter your message here</source>
                </trans-unit>
            </body>
        </file>
    </xliff>

Copy it to your target language and add the ``target-language`` attribute as well as the needed
``<target>…</target>`` entries.
