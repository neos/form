<?php
namespace Neos\Form\Core\Runtime;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Property\Exception;
use Neos\Form\Core\Model\FinisherContext;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Core\Renderer\RendererInterface;
use Neos\Form\Exception\PropertyMappingException;
use Neos\Form\Exception\RenderingException;
use Neos\Utility\Arrays;

/**
 * This class implements the *runtime logic* of a form, i.e. deciding which
 * page is shown currently, what the current values of the form are, trigger
 * validation and property mapping.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * You generally receive an instance of this class by calling {@link \Neos\Form\Core\Model\FormDefinition::bind}.
 *
 * Rendering a Form
 * ================
 *
 * That's easy, just call render() on the FormRuntime:
 *
 * /---code php
 * $form = $formDefinition->bind($request, $response);
 * $renderedForm = $form->render();
 * \---
 *
 * Accessing Form Values
 * =====================
 *
 * In order to get the values the user has entered into the form, you can access
 * this object like an array: If a form field with the identifier *firstName*
 * exists, you can do **$form['firstName']** to retrieve its current value.
 *
 * You can also set values in the same way.
 *
 * Rendering Internals
 * ===================
 *
 * The FormRuntime asks the FormDefinition about the configured Renderer
 * which should be used ({@link \Neos\Form\Core\Model\FormDefinition::getRendererClassName}),
 * and then trigger render() on this element.
 *
 * This makes it possible to declaratively define how a form should be rendered.
 *
 * @api
 */
class FormRuntime implements RootRenderableInterface, \ArrayAccess
{
    /**
     * @var FormDefinition
     * @internal
     */
    protected $formDefinition;

    /**
     * @var ActionRequest
     * @internal
     */
    protected $request;

    /**
     * @var ActionResponse
     * @internal
     */
    protected $response;

    /**
     * @var ActionResponse
     * @internal
     */
    protected $parentResponse;

    /**
     * @var FormState
     * @internal
     */
    protected $formState;

    /**
     * The current page is the page which will be displayed to the user
     * during rendering.
     *
     * If $currentPage is NULL, the *last* page has been submitted and
     * finishing actions need to take place. You should use $this->isAfterLastPage()
     * instead of explicitely checking for NULL.
     *
     * @var Page
     * @internal
     */
    protected $currentPage = null;

    /**
     * Reference to the page which has been shown on the last request (i.e.
     * we have to handle the submitted data from lastDisplayedPage)
     *
     * @var Page
     * @internal
     */
    protected $lastDisplayedPage = null;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Cryptography\HashService
     * @internal
     */
    protected $hashService;

    /**
     * @var callable[]
     */
    protected $renderCallbacks = [];

    /**
     * @param FormDefinition $formDefinition
     * @param ActionRequest $request
     * @param ActionResponse $response
     * @throws \Neos\Form\Exception\IdentifierNotValidException
     * @internal
     */
    public function __construct(FormDefinition $formDefinition, ActionRequest $request, ActionResponse $response)
    {
        $this->formDefinition = $formDefinition;
        $rootRequest = $request->getMainRequest() ?: $request;
        $pluginArguments = $rootRequest->getPluginArguments();
        $this->request = $request->createSubRequest();
        $formIdentifier = $this->formDefinition->getIdentifier();
        $this->request->setArgumentNamespace('--' . $formIdentifier);
        if (isset($pluginArguments[$formIdentifier])) {
            $this->request->setArguments($pluginArguments[$formIdentifier]);
        }

        $this->parentResponse = $response;
        $this->response = new ActionResponse();
    }

    /**
     * @return void
     * @internal
     */
    public function initializeObject()
    {
        $this->initializeFormStateFromRequest();
        $this->initializeCurrentPageFromRequest();

        if (!$this->isFirstRequest() && $this->getRequest()->getHttpRequest()->getMethod() === 'POST') {
            $this->processSubmittedFormValues();
        }
    }

    /**
     * @return void
     * @internal
     */
    protected function initializeFormStateFromRequest()
    {
        $serializedFormStateWithHmac = $this->request->getInternalArgument('__state');
        if ($serializedFormStateWithHmac === null) {
            $this->formState = new FormState();
        } else {
            $serializedFormState = $this->hashService->validateAndStripHmac($serializedFormStateWithHmac);
            $this->formState = unserialize(base64_decode($serializedFormState), ['allowed_classes' => [FormState::class]]);
        }
    }

    /**
     * @return void
     * @internal
     */
    protected function initializeCurrentPageFromRequest()
    {
        if (!$this->formState->isFormSubmitted()) {
            $this->currentPage = $this->formDefinition->getPageByIndex(0);
            return;
        }
        $this->lastDisplayedPage = $this->formDefinition->getPageByIndex($this->formState->getLastDisplayedPageIndex());

        // We know now that lastDisplayedPage is filled
        $currentPageIndex = (integer)$this->request->getInternalArgument('__currentPage');
        if ($currentPageIndex > $this->lastDisplayedPage->getIndex() + 1) {
            // We only allow jumps to following pages
            $currentPageIndex = $this->lastDisplayedPage->getIndex() + 1;
        }

        // We now know that the user did not try to skip a page
        if ($currentPageIndex === count($this->formDefinition->getPages())) {
            // Last Page
            $this->currentPage = null;
        } else {
            $this->currentPage = $this->formDefinition->getPageByIndex($currentPageIndex);
        }
    }

    /**
     * Returns TRUE if the last page of the form has been submitted, otherwise FALSE
     *
     * @return boolean
     */
    protected function isAfterLastPage()
    {
        return ($this->currentPage === null);
    }

    /**
     * Returns TRUE if no previous page is stored in the FormState, otherwise FALSE
     *
     * @return boolean
     */
    protected function isFirstRequest()
    {
        return ($this->lastDisplayedPage === null);
    }

    /**
     * @return void
     * @internal
     */
    protected function processSubmittedFormValues()
    {
        $result = $this->mapAndValidatePage($this->lastDisplayedPage);
        if ($result->hasErrors() && !$this->userWentBackToPreviousStep()) {
            $this->currentPage = $this->lastDisplayedPage;
            $this->request->setArgument('__submittedArguments', $this->request->getArguments());
            $this->request->setArgument('__submittedArgumentValidationResults', $result);
        }
    }

    /**
     * returns TRUE if the user went back to any previous step in the form.
     *
     * @return boolean
     */
    protected function userWentBackToPreviousStep()
    {
        return !$this->isAfterLastPage() && !$this->isFirstRequest() && $this->currentPage->getIndex() < $this->lastDisplayedPage->getIndex();
    }

    /**
     * @param Page $page
     * @return Result
     * @internal
     * @throws PropertyMappingException
     */
    protected function mapAndValidatePage(Page $page)
    {
        $result = new Result();
        $requestArguments = $this->request->getArguments();

        $propertyPathsForWhichPropertyMappingShouldHappen = [];
        $registerPropertyPaths = function ($propertyPath) use (&$propertyPathsForWhichPropertyMappingShouldHappen) {
            $propertyPathParts = explode('.', $propertyPath);
            $accumulatedPropertyPathParts = [];
            foreach ($propertyPathParts as $propertyPathPart) {
                $accumulatedPropertyPathParts[] = $propertyPathPart;
                $temporaryPropertyPath = implode('.', $accumulatedPropertyPathParts);
                $propertyPathsForWhichPropertyMappingShouldHappen[$temporaryPropertyPath] = $temporaryPropertyPath;
            }
        };

        foreach ($page->getElementsRecursively() as $element) {
            $value = Arrays::getValueByPath($requestArguments, $element->getIdentifier());
            $element->onSubmit($this, $value);

            $this->formState->setFormValue($element->getIdentifier(), $value);
            $registerPropertyPaths($element->getIdentifier());
        }

        // The more parts the path has, the more early it is processed
        usort($propertyPathsForWhichPropertyMappingShouldHappen, function ($a, $b) {
            return substr_count($b, '.') - substr_count($a, '.');
        });

        $processingRules = $this->formDefinition->getProcessingRules();
        foreach ($propertyPathsForWhichPropertyMappingShouldHappen as $propertyPath) {
            if (isset($processingRules[$propertyPath])) {
                $processingRule = $processingRules[$propertyPath];
                $value = $this->formState->getFormValue($propertyPath);
                try {
                    $value = $processingRule->process($value);
                } catch (Exception $exception) {
                    throw new PropertyMappingException('Failed to process FormValue at "' . $propertyPath . '" from "' . gettype($value) . '" to "' . $processingRule->getDataType() . '"', 1355218921, $exception);
                }
                $result->forProperty($propertyPath)->merge($processingRule->getProcessingMessages());
                $this->formState->setFormValue($propertyPath, $value);
            }
        }

        return $result;
    }

    /**
     * Override the current page taken from the request, rendering the page with index $pageIndex instead.
     *
     * This is typically not needed in production code, but it is very helpful when displaying
     * some kind of "preview" of the form.
     *
     * @param integer $pageIndex
     * @return void
     * @api
     */
    public function overrideCurrentPage($pageIndex)
    {
        $this->currentPage = $this->formDefinition->getPageByIndex($pageIndex);
    }

    /**
     * Render this form.
     *
     * @return string rendered form
     * @api
     * @throws RenderingException
     */
    public function render()
    {
        if ($this->isAfterLastPage()) {
            $this->invokeFinishers();

            // Do not provide content to parent request as that would overwrite in later mergers.
            $content = $this->response->getContent();
            $this->response->setContent('');
            $this->response->mergeIntoParentResponse($this->parentResponse);
            return $content;
        }

        $this->formState->setLastDisplayedPageIndex($this->currentPage->getIndex());

        if ($this->formDefinition->getRendererClassName() === null) {
            throw new RenderingException(sprintf('The form definition "%s" does not have a rendererClassName set.', $this->formDefinition->getIdentifier()), 1326095912);
        }
        $rendererClassName = $this->formDefinition->getRendererClassName();
        $renderer = new $rendererClassName();
        if (!($renderer instanceof RendererInterface)) {
            throw new RenderingException(sprintf('The renderer "%s" des not implement RendererInterface', $rendererClassName), 1326096024);
        }

        $controllerContext = $this->getControllerContext();
        $renderer->setControllerContext($controllerContext);

        $renderer->setFormRuntime($this);
        return $renderer->renderRenderable($this);
    }

    /**
     * Executes all finishers of this form
     *
     * @return void
     * @internal
     */
    protected function invokeFinishers()
    {
        $finisherContext = new FinisherContext($this);
        foreach ($this->formDefinition->getFinishers() as $finisher) {
            $finisher->execute($finisherContext);
            if ($finisherContext->isCancelled()) {
                break;
            }
        }
    }

    /**
     * @return string The identifier of underlying form
     * @api
     */
    public function getIdentifier()
    {
        return $this->formDefinition->getIdentifier();
    }

    /**
     * Get the request this object is bound to.
     *
     * This is mostly relevant inside Finishers, where you f.e. want to redirect
     * the user to another page.
     *
     * @return ActionRequest the request this object is bound to
     * @api
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response this object is bound to.
     *
     * This is mostly relevant inside Finishers, where you f.e. want to set response
     * headers or output content.
     *
     * @return ActionResponse the response this object is bound to
     * @api
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the currently selected page
     *
     * @return Page
     * @api
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Returns the previous page of the currently selected one or NULL if there is no previous page
     *
     * @return Page|null
     * @api
     */
    public function getPreviousPage()
    {
        $previousPageIndex = $this->currentPage->getIndex() - 1;
        if (!$this->formDefinition->hasPageWithIndex($previousPageIndex)) {
            return null;
        }
        return $this->formDefinition->getPageByIndex($previousPageIndex);
    }

    /**
     * Returns the next page of the currently selected one or NULL if there is no next page
     *
     * @return Page|null
     * @api
     */
    public function getNextPage()
    {
        $nextPageIndex = $this->currentPage->getIndex() + 1;
        if (!$this->formDefinition->hasPageWithIndex($nextPageIndex)) {
            return null;
        }
        return $this->formDefinition->getPageByIndex($nextPageIndex);
    }

    /**
     * @return ControllerContext
     * @internal
     */
    protected function getControllerContext()
    {
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($this->request);

        return new ControllerContext(
            $this->request,
            $this->response,
            new Arguments([]),
            $uriBuilder
        );
    }

    /**
     * Abstract "type" of this Renderable. Is used during the rendering process
     * to determine the template file or the View PHP class being used to render
     * the particular element.
     *
     * @return string
     * @api
     */
    public function getType()
    {
        return $this->formDefinition->getType();
    }

    /**
     * @param string $identifier
     * @return mixed
     * @api
     */
    public function offsetExists($identifier)
    {
        return ($this->getElementValue($identifier) !== null);
    }

    /**
     * Returns the value of the specified element
     *
     * @param string $identifier
     * @return mixed
     * @api
     */
    protected function getElementValue($identifier)
    {
        $formValue = $this->formState->getFormValue($identifier);
        if ($formValue !== null) {
            return $formValue;
        }
        return $this->formDefinition->getElementDefaultValueByIdentifier($identifier);
    }

    /**
     * @param string $identifier
     * @return mixed
     * @api
     */
    public function offsetGet($identifier)
    {
        return $this->getElementValue($identifier);
    }

    /**
     * @param string $identifier
     * @param mixed $value
     * @return void
     * @api
     */
    public function offsetSet($identifier, $value)
    {
        $this->formState->setFormValue($identifier, $value);
    }

    /**
     * @api
     * @param string $identifier
     * @return void
     */
    public function offsetUnset($identifier)
    {
        $this->formState->setFormValue($identifier, null);
    }

    /**
     * @return Page[] The Form's pages in the correct order
     * @api
     */
    public function getPages()
    {
        return $this->formDefinition->getPages();
    }

    /**
     * @return FormState
     * @internal
     */
    public function getFormState()
    {
        return $this->formState;
    }

    /**
     * @return string
     * @internal
     */
    public function getSerializedFormState()
    {
        $serializedFormState = base64_encode(serialize(clone $this->getFormState()));
        return $this->hashService->appendHmac($serializedFormState);
    }

    /**
     * Get all rendering options
     *
     * @return array associative array of rendering options
     * @api
     */
    public function getRenderingOptions()
    {
        return $this->formDefinition->getRenderingOptions();
    }

    /**
     * Get the renderer class name to be used to display this renderable;
     * must implement RendererInterface
     *
     * @return string the renderer class name
     * @api
     */
    public function getRendererClassName()
    {
        return $this->formDefinition->getRendererClassName();
    }

    /**
     * Get the label which shall be displayed next to the form element
     *
     * @return string
     * @api
     */
    public function getLabel()
    {
        return $this->formDefinition->getLabel();
    }

    /**
     * Get the underlying form definition from the runtime
     *
     * @return FormDefinition
     * @api
     */
    public function getFormDefinition()
    {
        return $this->formDefinition;
    }

    /**
     * This is a callback that is invoked by the Renderer before the corresponding element is rendered.
     * Use this to access previously submitted values and/or modify the $formRuntime before an element
     * is outputted to the browser.
     *
     * @param FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function beforeRendering(FormRuntime $formRuntime)
    {
    }

    /**
     * Registers a callback that is called just before a Form Element is rendered.
     * The callback will be invoked with the rendered element and an instance of the RootRenderableInterface as
     * arguments and is expected to return the (possibly altered) rendered element
     *
     * @param callable $callback
     * @return void
     * @api
     */
    public function registerRenderCallback(callable $callback)
    {
        $this->renderCallbacks[] = $callback;
    }

    /**
     * @param string $renderedElement
     * @param RootRenderableInterface $renderable
     * @return string
     */
    public function invokeRenderCallbacks($renderedElement, RootRenderableInterface $renderable)
    {
        foreach ($this->renderCallbacks as $renderCallback) {
            $renderedElement = call_user_func($renderCallback, $renderedElement, $renderable);
        }
        return $renderedElement;
    }
}
