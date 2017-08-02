<?php
namespace Kinkor\Generator\Parser;

class ExtensionParser {
	
	/**
	 * @var string
	 */
	protected $extension = '';
	
	/**
	 * @var bool
	 */
	protected $forceNamespace = FALSE;
	
	/**
	 * @var \ReflectionExtension
	 */
	protected $ref = NULL;
	
	/**
	 * @var array
	 */
	public $classes = [];
	
	/**
	 * @var array
	 */
	public $constants = [];
	
	/**
	 * @var array
	 */
	public $functions = [];
	
	/**
	 * ExtensionParser constructor.
	 *
	 * @param      $extensionName
	 * @param bool $forceNamespace
	 */
	public function __construct($extensionName, $forceNamespace = FALSE) {
		$this->extension = $extensionName;
		$this->forceNamespace = $forceNamespace;
		$this->ref = new \ReflectionExtension($this->extension);
		$this->getClasses();
		$this->getConstants();
		$this->getFunctions();
	}
	
	/**
	 *
	 */
	public function getClasses() {
		$classes = $this->ref->getClasses();
		foreach($classes as $class) {
			$parser = new ClassParser($class->getName(), $this->forceNamespace);
			$this->classes[] = $parser;
		}
	}
	
	/**
	 *
	 */
	public function getConstants() {
		$this->constants = $this->ref->getConstants();
	}
	
	/**
	 *
	 */
	public function getFunctions() {
		$functions = $this->ref->getFunctions();
		foreach($functions as $function) {
			$parameters = new ParameterParser($function->getParameters());
			$this->functions[] = [
				'name'       => $function->getName(),
				'parameters' => $parameters,
			];
		}
	}
	
}