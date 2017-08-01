<?php
namespace Kinkor\Generator\Parser;

class ClassParser {
	
	/**
	 * @var \ReflectionClass
	 */
	protected $ref = NULL;
	
	/**
	 * @var bool
	 */
	protected $forceNamespace = FALSE;
	
	/**
	 * @var array
	 */
	public $namespaceInfo = [];
	
	/**
	 * @var array
	 */
	public $classInfo = [];
	
	/**
	 * @var array
	 */
	public $constantInfo = [];
	
	/**
	 * @var array
	 */
	public $propertyInfo = [];
	
	/**
	 * @var array
	 */
	public $methodInfo = [];
	
	/**
	 * ClassParser constructor.
	 *
	 * @param $class
	 */
	public function __construct($class, $forceNamespace = FALSE) {
		$this->ref = new \ReflectionClass($class);
		$this->forceNamespace = $forceNamespace;
		$this->getNamespaceInfo();
		$this->getClassInfo();
		$this->getConstantInfo();
		$this->getPropertyInfo();
		$this->getMethodInfo();
		$this->forceNamespace();
	}
	
	/**
	 * @return array
	 */
	public function getNamespaceInfo() {
		if(!$this->namespaceInfo) {
			$this->namespaceInfo['name'] = $this->ref->getNamespaceName();
		}
		
		return $this->namespaceInfo;
	}
	
	/**
	 * @return array
	 */
	public function getClassInfo() {
		if(!$this->classInfo) {
			$this->classInfo['name'] = $this->ref->getShortName();
			$modifies = \Reflection::getModifierNames($this->ref->getModifiers());
			$this->classInfo['modify'] = implode(' ', $modifies);
			$this->classInfo['type'] = $this->ref->isInterface() ? 'interface' :
				($this->ref->isTrait() ? 'trait' : 'class');
			$parent = $this->ref->getParentClass();
			$this->classInfo['extends'] = $parent ? $parent->getName() : '';
			if($this->classInfo['extends'] && $this->namespaceInfo['name']) {
				$this->classInfo['extends'] = '\\' . $this->classInfo['extends'];
			}
			$this->classInfo['implements'] = [];
			foreach($this->ref->getInterfaceNames() as $name) {
				$this->classInfo['implements'][] = $this->namespaceInfo['name'] ? '\\' . $name : $name;
			}
		}
		
		return $this->classInfo;
	}
	
	/**
	 *
	 */
	public function forceNamespace() {
		if($this->forceNamespace && $this->ref->getNamespaceName() == '') {
			$name = $this->cnToNcn($this->ref->getName());
			$this->classInfo['name'] = array_pop($name);
			$this->namespaceInfo['name'] = $name ? implode('\\', $name) : '';
			if($this->namespaceInfo['name']) {
				if($this->classInfo['extends']) {
					$name = $this->cnToNcn($this->classInfo['extends']);
					$fullName = '\\' . implode('\\', $name);
					if(strpos($fullName, $this->namespaceInfo['name']) !== FALSE) {
						$extends = array_pop($name);
						$this->classInfo['extends'] = ($extends == $this->classInfo['name'] ? '' : $extends);
					} else {
						$this->classInfo['extends'] = $fullName;
					}
				}
				foreach($this->classInfo['implements'] as $key => $interface) {
					$name = $this->cnToNcn($interface);
					$fullName = '\\' . implode('\\', $name);
					if(strpos($fullName, $this->namespaceInfo['name']) !== FALSE) {
						$tmpName = array_pop($name);
						$this->classInfo['implements'][$key] = ($tmpName == $this->classInfo['name'] ? '' : $tmpName);
					} else {
						$this->classInfo['implements'][$key] = $fullName;
					}
				}
				foreach($this->methodInfo as &$item) {
					foreach($item['args'] as &$arg) {
						if($arg['modify']) {
							$name = $this->cnToNcn($arg['modify']);
							$arg['modify'] = '\\' . implode('\\', $name);
						}
					}
				}
			}
		}
	}
	
	public function cnToNcn($name) {
		$namespace = explode('_', $name);
		$nameArr = [];
		foreach($namespace as $v) {
			$nameArr[] = ucfirst($v);
		}
		
		return $nameArr;
	}
	
	/**
	 * @return array
	 */
	public function getConstantInfo() {
		if(!$this->constantInfo) {
			$constants = $this->ref->getConstants();
			foreach($constants as $k => $v) {
				$this->constantInfo[] = ['name' => $k, 'value' => $v];
			}
		}
		
		return $this->constantInfo;
	}
	
	/**
	 * @return array
	 */
	public function getPropertyInfo() {
		if(!$this->propertyInfo) {
			$properties = $this->ref->getProperties();
			foreach($properties as $property) {
				if(!$property->isPublic()) {
					$property->setAccessible(TRUE);
				}
				$modifies = \Reflection::getModifierNames($property->getModifiers());
				$item = [
					'name'   => '$' . $property->getName(),
					'modify' => implode(' ', $modifies),
					'doc'    => $property->getDocComment() ?: '',
				];
				$this->propertyInfo[] = $item;
			}
		}
		
		return $this->propertyInfo;
	}
	
	/**
	 * @return array
	 */
	public function getMethodInfo() {
		if(!$this->methodInfo) {
			$methods = $this->ref->getMethods();
			foreach($methods as $method) {
				$modifies = \Reflection::getModifierNames($method->getModifiers());
				$params = $method->getParameters();
				$args = [];
				foreach($params as $param) {
					$default = $param->isDefaultValueAvailable() ? ($param->isDefaultValueConstant() ?
						$param->getDefaultValueConstantName() : $param->getDefaultValue()) : '';
					$isOptional = $param->isOptional();
					$isReference = $param->isPassedByReference();
					$class = $param->getClass();
					$item = [
						'name'        => '$' . $param->getName(),
						'position'    => $param->getPosition(),
						'default'     => $default,
						'is_optional' => $isOptional,
						'modify'      => !$class ? '' : ($this->namespaceInfo['name'] ? '\\' . $class->getName() : $class->getShortName()),
					];
					!$isReference ?: $item['name'] = '&' . $item['name'];
					$args[] = $item;
				}
				$item = [
					'name'   => $method->getName(),
					'modify' => implode(' ', $modifies),
					'doc'    => $method->getDocComment() ?: '',
					'args'   => $args,
				];
				$this->methodInfo[] = $item;
			}
		}
		
		return $this->methodInfo;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		$str = [];
		!$this->namespaceInfo['name'] ?: $str[] = 'namespace ' . $this->namespaceInfo['name'] . ';' . PHP_EOL;
		$str[] = ($this->classInfo['modify'] ? $this->classInfo['modify'] . ' ' : '') .
			$this->classInfo['type'] . ' ' . $this->classInfo['name'] .
			($this->classInfo['extends'] ? ' extends ' . $this->classInfo['extends'] : '') .
			($this->classInfo['implements'] ? ' implements ' . implode(',',
					$this->classInfo['implements']) : '') . ' {' . PHP_EOL;
		foreach($this->constantInfo as $item) {
			$str[] = '    const ' . $item['name'] . '=' . $item['value'] . ';' . PHP_EOL;
		}
		foreach($this->propertyInfo as $item) {
			!$item['doc'] ?: $str[] = $item['doc'];
			$str[] = '    ' . ($item['modify'] ? $item['modify'] . ' ' : '') . $item['name'] . ';' . PHP_EOL;
		}
		foreach($this->methodInfo as $item) {
			!$item['doc'] ?: $str[] = $item['doc'];
			$argStr = [];
			foreach($item['args'] as $arg) {
				$argStr[] = ($arg['modify'] ? $arg['modify'] . ' ' : '') . $arg['name'] . ($arg['default'] ? '=' . $arg['default'] : '');
			}
			$argStr = implode(', ', $argStr);
			$argStr = '(' . $argStr . ')';
			$str[] = '    ' . ($item['modify'] ? $item['modify'] . ' ' : '') . 'function ' . $item['name'] . $argStr . ' { }' . PHP_EOL;
		}
		$str[] = '}';
		
		return implode(PHP_EOL, $str);
	}
	
}