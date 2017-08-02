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
				$isArray = $parameter->isArray();
				$isCall = $parameter->isCallable();
				$class = $parameter->getClass();
				$item = [
					'name'        => '$' . $parameter->getName(),
					'position'    => $parameter->getPosition(),
					'default'     => $isOptional ? ($isArray ? [] : ($isCall ? NULL : $default)) : $default,
					'is_optional' => $isOptional,
					'modify'      => !$class ? '' : ($this->useNamespace ? '\\' . $class->getName() : $class->getShortName()),
				];
				var_dump($item);
				!$isReference ?: $item['name'] = '&' . $item['name'];
				$this->args[] = $item;
			}
		}
		array_multisort(array_column($this->args, 'position'), SORT_ASC, $this->args);
	}
	
	protected function defaultValueToString($value) {
		switch(TRUE) {
			case is_null($value):
				return 'NULL';
			case is_array($value):
				return '[]';
			case is_string($value):
				return '\'' . $value . '\'';
			default:
				return $value;
		}
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		$argStr = [];
		foreach($this->args as $arg) {
			if($arg['is_optional']) {
				$argStr[] = ($arg['modify'] ? $arg['modify'] . ' ' : '') . $arg['name'] . ' = ' . $this->defaultValueToString($arg['default']);
			} else {
				$argStr[] = ($arg['modify'] ? $arg['modify'] . ' ' : '') . $arg['name'];
			}
		}
		
		return implode(', ', $argStr);
	}
	
}