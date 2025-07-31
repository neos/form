# Flow Form Framework

An extensible and flexible framework to **build web forms**.

See https://flow-form-framework.readthedocs.org/en/latest/ for a detailed documentation.

## Related Packages

This package implements the core of the `Flow Form Framework` and can be used directly within [Neos](https://neos.io) Websites or [Flow](https://flow.neos.io) Applications.
There are some useful related packages:

<table>
  <tr>
    <th>Neos Package Key</th>
    <th>Description</th>
    <th>Composer key / Packagist URL</th>
  </tr>
  <tr>
    <td><a href="https://github.com/neos/form">Neos.Form</a></td>
    <td>The actual Form Framework core (this package)</td>
    <td><a href="https://packagist.org/packages/neos/form">neos/form</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/neos/form-builder">Neos.Form.Builder</a></td>
    <td>A Form Builder IDE integrated to Neos CMS that allows for form *Definitions* to be created via the Backend interface and/or Fusion</td>
    <td><a href="https://packagist.org/packages/neos/form-builder">neos/form-builder</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/neos/form-yamlbuilder">Neos.Form.YamlBuilder</a><br><small>(formerly <code>Neos.FormBuilder</code>)</small></td>
    <td>The original Form Builder IDE that can be used with Flow alone to create YAML Form Definitions</td>
    <td><a href="https://packagist.org/packages/neos/form-yamlbuilder">neos/form-yamlbuilder</a><br><small>(formerly <code>neos/formbuilder</code>)</small></td>
  </tr>
  <tr>
    <td><a href="https://github.com/neos/form-fusionrenderer">Neos.Form.FusionRenderer</a></td>
    <td>A custom Form preset that allows Forms to be rendered via <a href="https://neos.readthedocs.io/en/stable/CreatingASite/Fusion/index.html">Fusion</a></td>
    <td><a href="https://packagist.org/packages/neos/form-fusionrenderer">neos/form-fusionrenderer</a></td>
  </tr>
  <tr>
    <th colspan="3">Third party packages</th>
  </tr>
    <tr>
    <td><a href="https://github.com/punktDe/form-persistence">PunktDe.FormPersistence</a></td>
    <td>Database persistence finisher, backend module to download data in different formats, export definition editor for field mapping, fine grained access rights, data retention mechanisms, ....</td>
    <td><a href="https://github.com/punktDe/form-persistence">punktDe/form-persistence</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/die-wegmeister/Wegmeister.DatabaseStorage">Wegmeister.DatabaseStorage</a></td>
    <td>Custom Form Finisher that helps storing formdata into the database and export it as Xlsx file</td>
    <td><a href="https://packagist.org/packages/wegmeister/databasestorage">wegmeister/databasestorage</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/die-wegmeister/Wegmeister.Recaptcha">Wegmeister.Recaptcha</a></td>
    <td>Custom Form Element that renders <a href="https://www.google.com/recaptcha">Googles reCAPTCHAs</a></td>
    <td><a href="https://packagist.org/packages/wegmeister/recaptcha">wegmeister/recaptcha</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/tmsdev/Tms.Cloudflare.Turnstile">Tms.Cloudflare.Turnstile</a></td>
    <td>Turnstile is Cloudflare’s smart CAPTCHA alternative. It can be embedded into any website without sending traffic through Cloudflare and works without showing visitors a CAPTCHA.</td>
    <td><a href="https://packagist.org/packages/tms/cloudflare-turnstile">tms/cloudflare-turnstile</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/tmsdev/Tms.Hcaptcha">Tms.Hcaptcha</a></td>
    <td>hCaptcha form element for Neos CMS</td>
    <td><a href="https://packagist.org/packages/tms/hcaptcha">tms/hcaptcha</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/daniellienert/honeypotformfield">Honeypot Field</a></td>
    <td>Honeypot form field to spam-protect your forms without further disturbance.</a></td>
    <td><a href="https://github.com/daniellienert/honeypotformfield">dl/honeypotformfield</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/bwaidelich/Wwwision.Form.ContentReferences">Wwwision.Form.ContentReferences</a></td>
    <td>Example Form Element that renders Neos Content References</td>
    <td><a href="https://packagist.org/packages/wwwision/form-contentreferences">wwwision/form-contentreferences</a></td>
  </tr>
  <tr>
    <td><a href="https://github.com/bwaidelich/Wwwision.Form.MultiColumnSection">Wwwision.Form.MultiColumnSection</a></td>
    <td>Example Section Form Element that renders child Form Elements in multiple columns</td>
    <td><a href="https://packagist.org/packages/wwwision/form-multicolumnsection">wwwision/form-multicolumnsection</a></td>
  </tr>
    <tr>
    <td><a href="https://github.com/bwaidelich/Wwwision.Form.MultiFileUpload">Wwwision.Form.MultiFileUpload</a></td>
    <td>Example package providing a simple MultiFileUpload element for the Neos.Form framework</td>
    <td><a href="https://packagist.org/packages/wwwision/form-multifileupload">wwwision/form-multifileupload</a></td>
  </tr>
  </tr>
    <tr>
    <td><a href="https://github.com/bwaidelich/Wwwision.Form.SecureFileUpload">Wwwision.Form.SecureFileUpload</a></td>
    <td>Examples and helpers for implementing secure form uploads</td>
    <td><a href="https://packagist.org/packages/wwwision/form-securefileupload">wwwision/form-securefileupload</a></td>
  </tr>
 </table>
 
 *Note: Feel free to create a Pull-Request with further related Form packages*
