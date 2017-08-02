<?php
namespace Kinkor\Generator\Parser;

class ParameterParser {
	
	/**
	 * @var array
	 */
	protected $params = [];
	
	/**
	 * @var bool
	 */
	protected $useNamespace = FALSE;
	
	/**
	 * @var array
	 */
	public $args = [];
	
	/**
	 * ParameterParser constructor.
	 *
	 * @param array $parameters
	 * @param bool  $useNamespace
	 */
	public function __construct($parameters, $useNamespace = FALSE) {
		$this->params = $parameters;
		$this->useNamespace = $useNamespace;
		$this->parser();
	}
	
	/**
	 *
	 */
	public function parser() {
		$this->args = [];
		foreach($this->params as $parameter) {
			if($parameter instanceof \ReflectionParameter) {
				$default = $parameter->isDefaultValueAvailable() ? ($parameter->isDefaultValueConstant() ?
					$parameter->getDefaultValueConstantName() : $parameter->getDefaultValue()) : '';
				$isOptional = $parameter->isOptional();
				$isReference = $parameter->isPassedByReference();
				$class = $parameter->getClass();
				$item = [
					'name'        => '$' . $parameter->getName(),
					'position'    => $parameter->getPosition(),
					'default'     => $default,
					'is_optional' => $isOptional,
					'modify'      => !$class ? '' : ($this->useNamespace ? '\\' . $class->getName() : $class->getShortName()),
				];
				!$isReference ?: $item['name'] = '&' . $item['name'];
				$this->args[] = $item;
			}
		}
		array_multisort(array_column($this->args, 'position'), SORT_ASC, $this->args);
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		$argStr = [];
		foreach($this->args as $arg) {
			$argStr[] = ($arg['modify'] ? $arg['modify'] . ' ' : '') . $arg['name'] . ($arg['default'] ? '=' . $arg['default'] : '');
		}
		
		return implode(', ', $argStr);
	}
	
}