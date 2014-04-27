<?php
namespace Kitsunet\ProxyObjects;

/**
 * A proxy object to help object method chaining without having to test for NULL all the way.
 * This takes inspiration from the Maybe monad.
 *
 */
class MaybeObject {

	/**
	 * @var object The object or NULL
	 */
	protected $object;

	/**
	 * @var \Closure
	 */
	protected $nullCallback;

	/**
	 * @param object $object The object or NULL
	 * @param callable $nullCallback
	 */
	public function __construct($object = NULL, \Closure $nullCallback = NULL) {
		$this->object = $object;
		$this->nullCallback = $nullCallback;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		if ($this->object === NULL) {
			if ($this->nullCallback !== NULL) {
				return $this->nullCallback->__invoke($method, $arguments);
			} else {
				return NULL;
			}
		}

		$result = call_user_func_array(array($this->object, $method), $arguments);
		if (is_object($result)) {
			return new MaybeObject($result, $this->nullCallback);
		} else {
			return $result;
		}
	}

	/**
	 *
	 * @param callable $nullHandler
	 * @return mixed
	 */
	public function __invoke(\Closure $nullHandler = NULL) {
		if ($nullHandler !== NULL && $this->object === NULL) {
			return $nullHandler->__invoke();
		}

		return $this->object;
	}

	/**
	 * @return boolean
	 */
	public function __isNull() {
		return ($this->object === NULL);
	}

	/**
	 * Set a callback function to be executed if a method was tried to be executed on the proxied object and it was in fact NULL.
	 * The callback receives two arguments,
	 * - string $name As first argument is the name of the called argument.
	 * - array $arguments The arguments given to the method.
	 *
	 * @param callable $callback
	 */
	public function __setNullCallback(\Closure $callback) {
		$this->nullCallback = $callback;
	}

}