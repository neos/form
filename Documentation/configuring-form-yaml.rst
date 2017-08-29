.. _configuring-form-yaml:

Configuring form rendering with YAML
====================================

Setup
-----

To render a form based on a YAML configuration file, simply use the Neos.Form ``render`` ViewHelper.
It uses the ``Neos\Form\Factory\ArrayFormFactory`` by default, which needs to know where the form
configuration is stored. This is done in ``Settings.yaml``:

.. code-block:: yaml

    Neos:
      Form:
        yamlPersistenceManager:
          savePath: 'resource://AcmeCom.SomePackage/Private/Form/'

From now on, every YAML file stored there can be loaded by using the filename as the persistence
identifier given to the ``render`` ViewHelper. So if you have a file named ``contact.yaml``, it
can be rendered with:

.. code-block:: html

    <form:render persistenceIdentifier="contact"/>

Form configuration
------------------

Generally speaking, the configuration is a nested structure that contains the keys ``type``, ``identifier`` and
``renderables`` and further options (e.g. ``label``) depending on the type of the current level.

The element types referenced below (``Neos.Form:SingleLineText`` and ``Neos.Form:MultiLineText``)
are just element types which are delivered by default by the framework. All available types can be
found in the settings of the Neos.Form package under ``Neos.Form.presets.default.formElementTypes``.

On the top level, the ``finishers`` can be configured as an array of ``identifier`` and ``options`` keys. The
available options depend on the finisher being used.

Let us examine the configuration for a basic contact form with the following structure:

* Contact Form *(Form)*
    * Page 01 *(Page)*
        * Name *(Single-line Text)*
        * Email *(Single-line Text)*
        * Message *(Multi-line Text)*

The following YAML is stored as ``contact.yaml``:

.. code-block:: yaml

    type: 'Neos.Form:Form'
    identifier: 'contact'
    label: 'Contact form'
    renderables:
      -
        type: 'Neos.Form:Page'
        identifier: 'page-one'
        renderables:
          -
            type: 'Neos.Form:SingleLineText'
            identifier: name
            label: 'Name'
            validators:
              - identifier: 'Neos.Flow:NotEmpty'
          -
            type: 'Neos.Form:SingleLineText'
            identifier: email
            label: 'Email'
            validators:
              - identifier: 'Neos.Flow:NotEmpty'
              - identifier: 'Neos.Flow:EmailAddress'
          -
            type: 'Neos.Form:MultiLineText'
            identifier: message
            label: 'Message'
            validators:
              - identifier: 'Neos.Flow:NotEmpty'
    finishers:
      -
        identifier: 'Neos.Form:Email'
        options:
          templatePathAndFilename: resource://AcmeCom.SomePackage/Private/Templates/Form/Contact.txt
          subject: '{subject}'
          recipientAddress: 'info@acme.com'
          recipientName: 'Acme Customer Care'
          senderAddress: '{email}'
          senderName: '{name}'
          format: plaintext

.. note:: Instead of setting the ``templatePathAndFilename`` option to specify the Fluid template file for the EmailFinisher,
          the template source can also be set directly via the ``templateSource`` option.


File Uploads
------------

The ``default`` preset comes with an ``FileUpload`` form element that allows the user of the form to upload arbitrary
files.
The ``EmailFinisher`` allows these files to be sent as attachments:

.. code-block:: yaml

    type: 'Neos.Form:Form'
    identifier: 'application'
    label: 'Example application form'
    renderables:
      -
        type: 'Neos.Form:Page'
        identifier: 'page-one'
        renderables:
          -
            type: 'Neos.Form:SingleLineText'
            identifier: email
            label: 'Email'
            validators:
              - identifier: 'Neos.Flow:NotEmpty'
              - identifier: 'Neos.Flow:EmailAddress'
          -
            type: 'Neos.Form:FileUpload'
            identifier: applicationform
            label: 'Application Form (PDF)'
            properties:
              allowedExtensions:
                - pdf
            validators:
              - identifier: 'Neos.Flow:NotEmpty'
    finishers:
      -
        # Application email that is sent to "customer care" with all uploaded files attached
        identifier: 'Neos.Form:Email'
        options:
          templatePathAndFilename: 'resource://AcmeCom.SomePackage/Private/Form/EmailTemplates/Application.html'
          subject: 'New Application'
          recipientAddress: 'application@acme.com'
          senderAddress: '{email}'
          format: html
          attachAllPersistentResources: true
      -
        # Confirmation email that is sent to the user with a static file attachment
        identifier: 'Neos.Form:Email'
        options:
          templatePathAndFilename: 'resource://AcmeCom.SomePackage/Private/Form/EmailTemplates/Confirmation.html'
          subject: 'Your Application'
          recipientAddress: '{email}'
          senderAddress: 'application@acme.com'
          format: html
          attachments:
            - resource: 'resource://AcmeCom.SomePackage/Private/Form/EmailTemplates/Attachments/TermsAndConditions.pdf'

.. note:: attachments can also referenced via `formElement` paths explicitly, for example: ``- formElement: 'image-field.resource'``