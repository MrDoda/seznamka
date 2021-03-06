<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\UI
 */



/**
 * Presenter component represents a webpage instance. It converts Request to IResponse.
 *
 * @author     David Grudl
 *
 * @property-read NPresenterRequest $request
 * @property-read array|NULL $signal
 * @property-read string $action
 * @property      string $view
 * @property      string $layout
 * @property-read \stdClass $payload
 * @property-read bool $ajax
 * @property-read NPresenterRequest $lastCreatedRequest
 * @property-read NSessionSection $flashSession
 * @property-read SystemContainer|NDIContainer $context
 * @property-read NApplication $application
 * @property-read NSession $session
 * @property-read NUser $user
 * @package Nette\Application\UI
 */
abstract class NPresenter extends NControl implements IPresenter
{
	/** bad link handling {@link NPresenter::$invalidLinkMode} */
	const INVALID_LINK_SILENT = 1,
		INVALID_LINK_WARNING = 2,
		INVALID_LINK_EXCEPTION = 3;

	/** @internal special parameter key */
	const SIGNAL_KEY = 'do',
		ACTION_KEY = 'action',
		FLASH_KEY = '_fid',
		DEFAULT_ACTION = 'default';

	/** @var int */
	public $invalidLinkMode;

	/** @var array of function(Presenter $sender, IResponse $response = NULL); Occurs when the presenter is shutting down */
	public $onShutdown;

	/** @var NPresenterRequest */
	private $request;

	/** @var IPresenterResponse */
	private $response;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/** @var bool  use absolute Urls or paths? */
	public $absoluteUrls = FALSE;

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var array */
	private $globalStateSinces;

	/** @var string */
	private $action;

	/** @var string */
	private $view;

	/** @var string */
	private $layout;

	/** @var \stdClass */
	private $payload;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var bool */
	private $startupCheck;

	/** @var NPresenterRequest */
	private $lastCreatedRequest;

	/** @var array */
	private $lastCreatedRequestFlag;

	/** @var SystemContainer|NDIContainer */
	private $context;


	public function __construct(NDIContainer $context = NULL)
	{
		$this->context = $context;
		if ($context && $this->invalidLinkMode === NULL) {
			$this->invalidLinkMode = $context->parameters['productionMode'] ? self::INVALID_LINK_SILENT : self::INVALID_LINK_WARNING;
		}
	}


	/**
	 * @return NPresenterRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 * Returns self.
	 * @return NPresenter
	 */
	public function getPresenter($need = TRUE)
	{
		return $this;
	}


	/**
	 * Returns a name that uniquely identifies component.
	 * @return string
	 */
	public function getUniqueId()
	{
		return '';
	}


	/********************* interface IPresenter ****************d*g**/


	/**
	 * @return IPresenterResponse
	 */
	public function run(NPresenterRequest $request)
	{
		try {
			// STARTUP
			$this->request = $request;
			$this->payload = new stdClass;
			$this->setParent($this->getParent(), $request->getPresenterName());

			if (!$this->getHttpResponse()->isSent()) {
				$this->getHttpResponse()->addHeader('Vary', 'X-Requested-With');
			}

			$this->initGlobalParameters();
			$this->checkRequirements($this->getReflection());
			$this->startup();
			if (!$this->startupCheck) {
				$class = $this->getReflection()->getMethod('startup')->getDeclaringClass()->getName();
				throw new InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
			}
			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->getAction()), $this->params);

			// autoload components
			foreach ($this->globalParams as $id => $foo) {
				$this->getComponent($id, FALSE);
			}

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}
			if ($this->getHttpRequest()->isMethod('head')) {
				$this->terminate();
			}

			// SIGNAL HANDLING
			// calls $this->handle<Signal>()
			$this->processSignal();

			// RENDERING VIEW
			$this->beforeRender();
			// calls $this->render<View>()
			$this->tryCall($this->formatRenderMethod($this->getView()), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->payload->state = $this->getGlobalState();
			}

			// finish template rendering
			$this->sendTemplate();

		} catch (NAbortException $e) {
			// continue with shutting down
			if ($this->isAjax()) try {
				$hasPayload = (array) $this->payload; unset($hasPayload['state']);
				if ($this->response instanceof NTextResponse && $this->isControlInvalid()) { // snippets - TODO
					$this->snippetMode = TRUE;
					$this->response->send($this->getHttpRequest(), $this->getHttpResponse());
					$this->sendPayload();

				} elseif (!$this->response && $hasPayload) { // back compatibility for use terminate() instead of sendPayload()
					$this->sendPayload();
				}
			} catch (NAbortException $e) { }

			if ($this->hasFlashSession()) {
				$this->getFlashSession()->setExpiration($this->response instanceof NRedirectResponse ? '+ 30 seconds' : '+ 3 seconds');
			}

			// SHUTDOWN
			$this->onShutdown($this, $this->response);
			$this->shutdown($this->response);

			return $this->response;
		}
	}


	/**
	 * @return void
	 */
	protected function startup()
	{
		$this->startupCheck = TRUE;
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function afterRender()
	{
	}


	/**
	 * @param  IPresenterResponse
	 * @return void
	 */
	protected function shutdown($response)
	{
	}


	/**
	 * Checks authorization.
	 * @return void
	 */
	public function checkRequirements($element)
	{
		$user = (array) $element->getAnnotation('User');
		if (in_array('loggedIn', $user) && !$this->getUser()->isLoggedIn()) {
			throw new NForbiddenRequestException;
		}
	}


	/********************* signal handling ****************d*g**/


	/**
	 * @return void
	 * @throws NBadSignalException
	 */
	public function processSignal()
	{
		if ($this->signal === NULL) {
			return;
		}

		try {
			$component = $this->signalReceiver === '' ? $this : $this->getComponent($this->signalReceiver, FALSE);
		} catch (InvalidArgumentException $e) {}

		if (isset($e) || $component === NULL) {
			throw new NBadSignalException("The signal receiver component '$this->signalReceiver' is not found.", NULL, isset($e) ? $e : NULL);

		} elseif (!$component instanceof ISignalReceiver) {
			throw new NBadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");
		}

		$component->signalReceived($this->signal);
		$this->signal = NULL;
	}


	/**
	 * Returns pair signal receiver and name.
	 * @return array|NULL
	 */
	public function getSignal()
	{
		return $this->signal === NULL ? NULL : array($this->signalReceiver, $this->signal);
	}


	/**
	 * Checks if the signal receiver is the given one.
	 * @param  mixed  component or its id
	 * @param  string signal name (optional)
	 * @return bool
	 */
	public function isSignalReceiver($component, $signal = NULL)
	{
		if ($component instanceof NComponent) {
			$component = $component === $this ? '' : $component->lookupPath(__CLASS__, TRUE);
		}

		if ($this->signal === NULL) {
			return FALSE;

		} elseif ($signal === TRUE) {
			return $component === ''
				|| strncmp($this->signalReceiver . '-', $component . '-', strlen($component) + 1) === 0;

		} elseif ($signal === NULL) {
			return $this->signalReceiver === $component;

		} else {
			return $this->signalReceiver === $component && strcasecmp($signal, $this->signal) === 0;
		}
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns current action name.
	 * @return string
	 */
	public function getAction($fullyQualified = FALSE)
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->action : $this->action;
	}


	/**
	 * Changes current action. Only alphanumeric characters are allowed.
	 * @param  string
	 * @return void
	 */
	public function changeAction($action)
	{
		if (is_string($action) && NStrings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')) {
			$this->action = $action;
			$this->view = $action;

		} else {
			$this->error('Action name is not alphanumeric string.');
		}
	}


	/**
	 * Returns current view.
	 * @return string
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Changes current view. Any name is allowed.
	 * @param  string
	 * @return self
	 */
	public function setView($view)
	{
		$this->view = (string) $view;
		return $this;
	}


	/**
	 * Returns current layout name.
	 * @return string|FALSE
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Changes or disables layout.
	 * @param  string|FALSE
	 * @return self
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout === FALSE ? FALSE : (string) $layout;
		return $this;
	}


	/**
	 * @return void
	 * @throws NBadRequestException if no template found
	 * @throws NAbortException
	 */
	public function sendTemplate()
	{
		$template = $this->getTemplate();
		if (!$template) {
			return;
		}

		if ($template instanceof IFileTemplate && !$template->getFile()) { // content template
			$files = $this->formatTemplateFiles();
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
				$file = strtr($file, '/', DIRECTORY_SEPARATOR);
				$this->error("Page not found. Missing template '$file'.");
			}
		}

		$this->sendResponse(new NTextResponse($template));
	}


	/**
	 * Finds layout template file name.
	 * @return string
	 */
	public function findLayoutTemplateFile()
	{
		if ($this->layout === FALSE) {
			return;
		}
		$files = $this->formatLayoutTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				return $file;
			}
		}

		if ($this->layout) {
			$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
			$file = strtr($file, '/', DIRECTORY_SEPARATOR);
			throw new FileNotFoundException("Layout not found. Missing template '$file'.");
		}
	}


	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$name = $this->getName();
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$layout = $this->layout ? $this->layout : 'layout';
		$dir = dirname($this->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		$list = array(
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
			"$dir/templates/$presenter/@$layout.phtml",
			"$dir/templates/$presenter.@$layout.phtml",
		);
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$list[] = "$dir/templates/@$layout.phtml";
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
		return $list;
	}


	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$name = $this->getName();
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname($this->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		return array(
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
			"$dir/templates/$presenter/$this->view.phtml",
			"$dir/templates/$presenter.$this->view.phtml",
		);
	}


	/**
	 * Formats action method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatActionMethod($action)
	{
		return 'action' . $action;
	}


	/**
	 * Formats render view method name.
	 * @param  string
	 * @return string
	 */
	protected static function formatRenderMethod($view)
	{
		return 'render' . $view;
	}


	/********************* partial AJAX rendering ****************d*g**/


	/**
	 * @return \stdClass
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Is AJAX request?
	 * @return bool
	 */
	public function isAjax()
	{
		if ($this->ajaxMode === NULL) {
			$this->ajaxMode = $this->getHttpRequest()->isAjax();
		}
		return $this->ajaxMode;
	}


	/**
	 * Sends AJAX payload to the output.
	 * @return void
	 * @throws NAbortException
	 */
	public function sendPayload()
	{
		$this->sendResponse(new NJsonResponse($this->payload));
	}


	/********************* navigation & flow ****************d*g**/


	/**
	 * Sends response and terminates presenter.
	 * @return void
	 * @throws NAbortException
	 */
	public function sendResponse(IPresenterResponse $response)
	{
		$this->response = $response;
		$this->terminate();
	}


	/**
	 * Correctly terminates presenter.
	 * @return void
	 * @throws NAbortException
	 */
	public function terminate()
	{
		if (func_num_args() !== 0) {
			trigger_error(__METHOD__ . ' is not intended to send a Application\Response; use sendResponse() instead.', E_USER_WARNING);
			$this->sendResponse(func_get_arg(0));
		}
		throw new NAbortException();
	}


	/**
	 * Forward to another presenter or action.
	 * @param  string|Request
	 * @param  array|mixed
	 * @return void
	 * @throws NAbortException
	 */
	public function forward($destination, $args = array())
	{
		if ($destination instanceof NPresenterRequest) {
			$this->sendResponse(new NForwardResponse($destination));
		}

		$_args=func_get_args(); $this->createRequest($this, $destination, is_array($args) ? $args : array_slice($_args, 1), 'forward');
		$this->sendResponse(new NForwardResponse($this->lastCreatedRequest));
	}


	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws NAbortException
	 */
	public function redirectUrl($url, $code = NULL)
	{
		if ($this->isAjax()) {
			$this->payload->redirect = (string) $url;
			$this->sendPayload();

		} elseif (!$code) {
			$code = $this->getHttpRequest()->isMethod('post')
				? IHttpResponse::S303_POST_GET
				: IHttpResponse::S302_FOUND;
		}
		$this->sendResponse(new NRedirectResponse($url, $code));
	}

	/** @deprecated */
	function redirectUri($url, $code = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use ' . __CLASS__ . '::redirectUrl() instead.', E_USER_WARNING);
		$this->redirectUrl($url, $code);
	}


	/**
	 * Throws HTTP error.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws NBadRequestException
	 */
	public function error($message = NULL, $code = IHttpResponse::S404_NOT_FOUND)
	{
		throw new NBadRequestException($message, $code);
	}


	/**
	 * Link to myself.
	 * @return string
	 */
	public function backlink()
	{
		return $this->getAction(TRUE);
	}


	/**
	 * Returns the last created Request.
	 * @return NPresenterRequest
	 */
	public function getLastCreatedRequest()
	{
		return $this->lastCreatedRequest;
	}


	/**
	 * Returns the last created Request flag.
	 * @param  string
	 * @return bool
	 */
	public function getLastCreatedRequestFlag($flag)
	{
		return !empty($this->lastCreatedRequestFlag[$flag]);
	}


	/**
	 * Conditional redirect to canonicalized URI.
	 * @return void
	 * @throws NAbortException
	 */
	public function canonicalize()
	{
		if (!$this->isAjax() && ($this->request->isMethod('get') || $this->request->isMethod('head'))) {
			try {
				$url = $this->createRequest($this, $this->action, $this->getGlobalState() + $this->request->getParameters(), 'redirectX');
			} catch (NInvalidLinkException $e) {}
			if (isset($url) && !$this->getHttpRequest()->getUrl()->isEqual($url)) {
				$this->sendResponse(new NRedirectResponse($url, IHttpResponse::S301_MOVED_PERMANENTLY));
			}
		}
	}


	/**
	 * Attempts to cache the sent entity by its last modification date.
	 * @param  string|int|DateTime  last modified time
	 * @param  string strong entity tag validator
	 * @param  mixed  optional expiration time
	 * @return void
	 * @throws NAbortException
	 * @deprecated
	 */
	public function lastModified($lastModified, $etag = NULL, $expire = NULL)
	{
		if ($expire !== NULL) {
			$this->getHttpResponse()->setExpiration($expire);
		}

		if (!$this->getHttpContext()->isModified($lastModified, $etag)) {
			$this->terminate();
		}
	}


	/**
	 * Request/URL factory.
	 * @param  NPresenterComponent  base
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!" or "this"
	 * @param  array    array of arguments
	 * @param  string   forward|redirect|link
	 * @return string   URL
	 * @throws NInvalidLinkException
	 * @internal
	 */
	protected function createRequest($component, $destination, array $args, $mode)
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		// cached services for better performance
		static $presenterFactory, $router, $refUrl;
		if ($presenterFactory === NULL) {
			$presenterFactory = $this->getApplication()->getPresenterFactory();
			$router = $this->getApplication()->getRouter();
			$refUrl = new NUrl($this->getHttpRequest()->getUrl());
			$refUrl->setPath($this->getHttpRequest()->getUrl()->getScriptPath());
		}

		$this->lastCreatedRequest = $this->lastCreatedRequestFlag = NULL;

		// PARSE DESTINATION
		// 1) fragment
		$a = strpos($destination, '#');
		if ($a === FALSE) {
			$fragment = '';
		} else {
			$fragment = substr($destination, $a);
			$destination = substr($destination, 0, $a);
		}

		// 2) ?query syntax
		$a = strpos($destination, '?');
		if ($a !== FALSE) {
			parse_str(substr($destination, $a + 1), $args); // requires disabled magic quotes
			$destination = substr($destination, 0, $a);
		}

		// 3) URL scheme
		$a = strpos($destination, '//');
		if ($a === FALSE) {
			$scheme = FALSE;
		} else {
			$scheme = substr($destination, 0, $a);
			$destination = substr($destination, $a + 2);
		}

		// 4) signal or empty
		if (!$component instanceof NPresenter || substr($destination, -1) === '!') {
			$signal = rtrim($destination, '!');
			$a = strrpos($signal, ':');
			if ($a !== FALSE) {
				$component = $component->getComponent(strtr(substr($signal, 0, $a), ':', '-'));
				$signal = (string) substr($signal, $a + 1);
			}
			if ($signal == NULL) {  // intentionally ==
				throw new NInvalidLinkException("Signal must be non-empty string.");
			}
			$destination = 'this';
		}

		if ($destination == NULL) {  // intentionally ==
			throw new NInvalidLinkException("Destination must be non-empty string.");
		}

		// 5) presenter: action
		$current = FALSE;
		$a = strrpos($destination, ':');
		if ($a === FALSE) {
			$action = $destination === 'this' ? $this->action : $destination;
			$presenter = $this->getName();
			$presenterClass = get_class($this);

		} else {
			$action = (string) substr($destination, $a + 1);
			if ($destination[0] === ':') { // absolute
				if ($a < 2) {
					throw new NInvalidLinkException("Missing presenter name in '$destination'.");
				}
				$presenter = substr($destination, 1, $a - 1);

			} else { // relative
				$presenter = $this->getName();
				$b = strrpos($presenter, ':');
				if ($b === FALSE) { // no module
					$presenter = substr($destination, 0, $a);
				} else { // with module
					$presenter = substr($presenter, 0, $b + 1) . substr($destination, 0, $a);
				}
			}
			try {
				$presenterClass = $presenterFactory->getPresenterClass($presenter);
			} catch (NInvalidPresenterException $e) {
				throw new NInvalidLinkException($e->getMessage(), NULL, $e);
			}
		}

		// PROCESS SIGNAL ARGUMENTS
		if (isset($signal)) { // $component must be IStatePersistent
			$reflection = new NPresenterComponentReflection(get_class($component));
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new NInvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === FALSE) { // TODO: AppForm exception
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!$reflection->hasCallableMethod($method)) {
					throw new NInvalidLinkException("Unknown signal '$signal', missing handler {$reflection->name}::$method()");
				}
				if ($args) { // convert indexed parameters to named
					self::argsToParams(get_class($component), $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $this) {
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($action === '') {
				$action = self::DEFAULT_ACTION;
			}

			$current = ($action === '*' || strcasecmp($action, $this->action) === 0) && $presenterClass === get_class($this); // TODO

			$reflection = new NPresenterComponentReflection($presenterClass);
			if ($args || $destination === 'this') {
				// counterpart of run() & tryCall()
				$method = call_user_func(array($presenterClass, 'formatActionMethod'), $action);
				if (!$reflection->hasCallableMethod($method)) {
					$method = call_user_func(array($presenterClass, 'formatRenderMethod'), $action);
					if (!$reflection->hasCallableMethod($method)) {
						$method = NULL;
					}
				}

				// convert indexed parameters to named
				if ($method === NULL) {
					if (array_key_exists(0, $args)) {
						throw new NInvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
					}

				} elseif ($destination === 'this') {
					self::argsToParams($presenterClass, $method, $args, $this->params);

				} else {
					self::argsToParams($presenterClass, $method, $args);
				}
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveState($args, $reflection);
			}

			if ($mode === 'redirect') {
				$this->saveGlobalState();
			}

			$globalState = $this->getGlobalState($destination === 'this' ? NULL : $presenterClass);
			if ($current && $args) {
				$tmp = $globalState + $this->params;
				foreach ($args as $key => $val) {
					if (http_build_query(array($val)) !== (isset($tmp[$key]) ? http_build_query(array($tmp[$key])) : '')) {
						$current = FALSE;
						break;
					}
				}
			}
			$args += $globalState;
		}

		// ADD ACTION & SIGNAL & FLASH
		if ($action) {
			$args[self::ACTION_KEY] = $action;
		}
		if (!empty($signal)) {
			$args[self::SIGNAL_KEY] = $component->getParameterId($signal);
			$current = $current && $args[self::SIGNAL_KEY] === $this->getParameter(self::SIGNAL_KEY);
		}
		if (($mode === 'redirect' || $mode === 'forward') && $this->hasFlashSession()) {
			$args[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		}

		$this->lastCreatedRequest = new NPresenterRequest(
			$presenter,
			NPresenterRequest::FORWARD,
			$args,
			array(),
			array()
		);
		$this->lastCreatedRequestFlag = array('current' => $current);

		if ($mode === 'forward' || $mode === 'test') {
			return;
		}

		// CONSTRUCT URL
		$url = $router->constructUrl($this->lastCreatedRequest, $refUrl);
		if ($url === NULL) {
			unset($args[self::ACTION_KEY]);
			$params = urldecode(http_build_query($args, NULL, ', '));
			throw new NInvalidLinkException("No route for $presenter:$action($params)");
		}

		// make URL relative if possible
		if ($mode === 'link' && $scheme === FALSE && !$this->absoluteUrls) {
			$hostUrl = $refUrl->getHostUrl() . '/';
			if (strncmp($url, $hostUrl, strlen($hostUrl)) === 0) {
				$url = substr($url, strlen($hostUrl) - 1);
			}
		}

		return $url . $fragment;
	}


	/**
	 * Converts list of arguments to named parameters.
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @return void
	 * @throws NInvalidLinkException
	 */
	private static function argsToParams($class, $method, & $args, $supplemental = array())
	{
		$i = 0;
		$rm = new ReflectionMethod($class, $method);
		foreach ($rm->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];

			} else {
				continue;
			}

			if ($args[$name] === NULL) {
				continue;
			}

			$def = $param->isDefaultValueAvailable() && $param->isOptional() ? $param->getDefaultValue() : NULL; // see PHP bug #62988
			$type = $param->isArray() ? 'array' : gettype($def);
			if (!NPresenterComponentReflection::convertType($args[$name], $type)) {
				throw new NInvalidLinkException("Invalid value for parameter '$name' in method $class::$method(), expected " . ($type === 'NULL' ? 'scalar' : $type) . ".");
			}

			if ($args[$name] === $def || ($def === NULL && is_scalar($args[$name]) && (string) $args[$name] === '')) {
				$args[$name] = NULL; // value transmit is unnecessary
			}
		}

		if (array_key_exists($i, $args)) {
			$method = $rm->getName();
			throw new NInvalidLinkException("Passed more parameters than method $class::$method() expects.");
		}
	}


	/**
	 * Invalid link handler. Descendant can override this method to change default behaviour.
	 * @return string
	 * @throws NInvalidLinkException
	 */
	protected function handleInvalidLink(NInvalidLinkException $e)
	{
		if ($this->invalidLinkMode === self::INVALID_LINK_SILENT) {
			return '#';

		} elseif ($this->invalidLinkMode === self::INVALID_LINK_WARNING) {
			return 'error: ' . $e->getMessage();

		} else { // self::INVALID_LINK_EXCEPTION
			throw $e;
		}
	}


	/********************* request serialization ****************d*g**/


	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = NStrings::random(5);
		} while (isset($session[$key]));

		$session[$key] = array($this->getUser()->getId(), $this->request);
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(NPresenterRequest::RESTORED, TRUE);
		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		$request->setParameters($params);
		$this->sendResponse(new NForwardResponse($request));
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Returns array of persistent components.
	 * This default implementation detects components by class-level annotation @persistent(cmp1, cmp2).
	 * @return array
	 */
	public static function getPersistentComponents()
	{
		return (array) NClassReflection::from(func_get_arg(0))
			->getAnnotation('persistent');
	}


	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 * @return array
	 */
	private function getGlobalState($forClass = NULL)
	{
		$sinces = & $this->globalStateSinces;

		if ($this->globalState === NULL) {
			$state = array();
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}
			$this->saveState($state, $forClass ? new NPresenterComponentReflection($forClass) : NULL);

			if ($sinces === NULL) {
				$sinces = array();
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(TRUE, 'IStatePersistent');

			foreach ($iterator as $name => $component) {
				if ($iterator->getDepth() === 0) {
					// counts with NRecursiveIteratorIterator::SELF_FIRST
					$since = isset($components[$name]['since']) ? $components[$name]['since'] : FALSE; // FALSE = nonpersistent
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				$params = array();
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since;
				}
			}

		} else {
			$state = $this->globalState;
		}

		if ($forClass !== NULL) {
			$since = NULL;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NAME_SEPARATOR);
					$x = $x === FALSE ? $key : substr($key, 0, $x);
					$sinces[$key] = isset($sinces[$x]) ? $sinces[$x] : FALSE;
				}
				if ($since !== $sinces[$key]) {
					$since = $sinces[$key];
					$ok = $since && (is_subclass_of($forClass, $since) || $forClass === $since);
				}
				if (!$ok) {
					unset($state[$key]);
				}
			}
		}

		return $state;
	}


	/**
	 * Permanently saves state information for all subcomponents to $this->globalState.
	 * @return void
	 */
	protected function saveGlobalState()
	{
		$this->globalParams = array();
		$this->globalState = $this->getGlobalState();
	}


	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->action, $this->view. Called by run().
	 * @return void
	 * @throws NBadRequestException if action name is not valid
	 */
	private function initGlobalParameters()
	{
		// init $this->globalParams
		$this->globalParams = array();
		$selfParams = array();

		$params = $this->request->getParameters();
		if ($this->isAjax()) {
			$params += $this->request->getPost();
		}

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::DEFAULT_ACTION);

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $this->getUniqueId();
		if (isset($selfParams[self::SIGNAL_KEY])) {
			$param = $selfParams[self::SIGNAL_KEY];
			if (!is_string($param)) {
				$this->error('Signal name is not string.');
			}
			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $this->getUniqueId();
				$this->signal = $param;
			}
			if ($this->signal == NULL) { // intentionally ==
				$this->signal = NULL;
			}
		}

		$this->loadState($selfParams);
	}


	/**
	 * Pops parameters for specified component.
	 * @param  string  component id
	 * @return array
	 */
	public function popGlobalParameters($id)
	{
		if (isset($this->globalParams[$id])) {
			$res = $this->globalParams[$id];
			unset($this->globalParams[$id]);
			return $res;

		} else {
			return array();
		}
	}


	/********************* flash session ****************d*g**/


	/**
	 * Checks if a flash session namespace exists.
	 * @return bool
	 */
	public function hasFlashSession()
	{
		return !empty($this->params[self::FLASH_KEY])
			&& $this->getSession()->hasSection('Nette.Application.Flash/' . $this->params[self::FLASH_KEY]);
	}


	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 * @return NSessionSection
	 */
	public function getFlashSession()
	{
		if (empty($this->params[self::FLASH_KEY])) {
			$this->params[self::FLASH_KEY] = NStrings::random(4);
		}
		return $this->getSession('Nette.Application.Flash/' . $this->params[self::FLASH_KEY]);
	}


	/********************* services ****************d*g**/


	/**
	 * @return void
	 */
	public function injectPrimary(NDIContainer $context)
	{
		$this->context = $context;
	}


	/**
	 * Gets the context.
	 * @return SystemContainer|NDIContainer
	 */
	public function getContext()
	{
		return $this->context;
	}


	/**
	 * @deprecated
	 */
	public function getService($name)
	{
		return $this->context->getService($name);
	}


	/**
	 * @return NHttpRequest
	 */
	protected function getHttpRequest()
	{
		return $this->context->getByType('IHttpRequest');
	}


	/**
	 * @return NHttpResponse
	 */
	protected function getHttpResponse()
	{
		return $this->context->getByType('IHttpResponse');
	}


	/**
	 * @return NHttpContext
	 */
	protected function getHttpContext()
	{
		return $this->context->getByType('NHttpContext');
	}


	/**
	 * @return NApplication
	 */
	public function getApplication()
	{
		return $this->context->getByType('NApplication');
	}


	/**
	 * @param  string
	 * @return NSession|NSessionSection
	 */
	public function getSession($namespace = NULL)
	{
		$handler = $this->context->getByType('NSession');
		return $namespace === NULL ? $handler : $handler->getSection($namespace);
	}


	/**
	 * @return NUser
	 */
	public function getUser()
	{
		return $this->context->getByType('NUser');
	}

}
