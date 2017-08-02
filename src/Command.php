<?php
namespace Kinkor\Generator;

use Kinkor\Generator\Parser\ClassParser;
use Kinkor\Generator\Parser\ExtensionParser;

class Command {
	
	/**
	 * @param $argv
	 */
	public static function run($argv) {
		unset($argv[0]);
		$argv = array_values($argv);
		$command = new Command();
		if(!$argv || $argv[0] == '--help') {
			$command->help();
		}
		try {
			$params = $command->analysisParam(array_values($argv));
			$output = isset($params['output']) ? $params['output'] : '/tmp';
			unset($params['output']);
			if(!file_exists($output)) {
				throw new \Exception('Output path not exists.');
			}
			if(isset($params['extension'])) {
				$parser = $command->parserExtension($params['extension'],
					isset($params['force-namespace']) ? TRUE : FALSE);
				foreach($parser->classes as $class) {
					$command->outputClass($output, $class);
				}
				$command->outputConstant($output, $parser->constants, $params['extension'] . '_constant.php');
				$command->outputFunction($output, $parser->functions, $params['extension'] . '_function.php');
			}
			if(isset($params['class'])) {
				$parser = $command->parserClass($params['class'], isset($params['force-namespace']) ? TRUE : FALSE);
				$command->outputClass($output, $parser);
			}
		} catch(\Exception $e) {
			echo 'Exception Message: ' . $e->getMessage() . PHP_EOL;
			$command->help();
		}
	}
	
	/**
	 * @param $argv
	 *
	 * @return array
	 */
	public function analysisParam($argv) {
		$params = [];
		foreach($argv as $item) {
			$item = explode('=', $item);
			$item[0] = substr($item[0], 2);
			$params[ $item[0] ] = isset($item[1]) ? $item[1] : TRUE;
			
		}
		
		return $params;
	}
	
	/**
	 * @param string $str
	 */
	public function help($str = '') {
		switch($str) {
			default:
				echo <<<EOF
Usage: generator options
  
Options:
  --extension=extension-name Generator code this extension.
  --class=class-name         Generator code this class.
  --output=/tmp              Output file directory path(Default: /tmp).
  --force-namespace          Force use namespace generator code.
  --help                     Print this usage information.
EOF;
				echo PHP_EOL;
		}
		exit;
	}
	
	/**
	 * @param      $class
	 * @param bool $forceNamespace
	 *
	 * @return ClassParser
	 * @throws \Exception
	 */
	public function parserClass($class, $forceNamespace = FALSE) {
		if(!class_exists($class)) {
			throw new \Exception('Class not exists.');
		}
		$parser = new ClassParser($class, $forceNamespace);
		
		return $parser;
	}
	
	/**
	 * @param      $extensionName
	 * @param bool $forceNamespace
	 *
	 * @return ExtensionParser
	 * @throws \Exception
	 */
	public function parserExtension($extensionName, $forceNamespace = FALSE) {
		if(!extension_loaded($extensionName)) {
			throw new \Exception($extensionName . ' extension not load.');
		}
		$parser = new ExtensionParser($extensionName, $forceNamespace);
		
		return $parser;
	}
	
	public function outputClass($output, ClassParser $parser) {
		$dirs = explode('\\', $parser->namespaceInfo['name']);
		foreach($dirs as $dir) {
			$output .= '/' . $dir;
			if(!file_exists($output)) {
				mkdir($output);
			}
		}
		$filename = $parser->classInfo['name'] . '.php';
		if(file_exists($output . '/' . $filename)) {
			@unlink($output . '/' . $filename);
		}
		file_put_contents($output . '/' . $filename, '<?php' . PHP_EOL . $parser);
	}
	
	public function outputConstant($output, $constants, $filename) {
		if(!file_exists($output)) {
			mkdir($output);
		}
		if(file_exists($output . '/' . $filename)) {
			@unlink($output . '/' . $filename);
		}
		$content = [];
		foreach($constants as $name => $value) {
			if(is_numeric($value)) {
				$content[] = sprintf('define("%s", %s);', $name, $value);
			} else {
				$content[] = sprintf('define("%s", "%s");', $name, $value);
			}
		}
		$content = implode(PHP_EOL, $content);
		file_put_contents($output . '/' . $filename, '<?php' . PHP_EOL . $content);
	}
	
	public function outputFunction($output, $functions, $filename) {
		if(!file_exists($output)) {
			mkdir($output);
		}
		if(file_exists($output . '/' . $filename)) {
			@unlink($output . '/' . $filename);
		}
		$content = [];
		foreach($functions as $item) {
			$content[] = sprintf('function %s (%s) {}', $item['name'], $item['parameters']);
		}
		$content = implode(PHP_EOL, $content);
		file_put_contents($output . '/' . $filename, '<?php' . PHP_EOL . $content);
	}
}