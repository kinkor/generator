<?php
namespace Kinkor\Generator;

use Kinkor\Generator\Parser\ClassParser;

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
				$command->parserExtension($params['extension'], isset($params['force-namespace']) ? TRUE : FALSE);
			}
			if(isset($params['class'])) {
				$parser = $command->parserClass($params['class'], isset($params['force-namespace']) ? TRUE : FALSE);
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
				print <<<EOF
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
	
	public function parserClass($class, $forceNamespace = FALSE) {
		if(!class_exists($class)) {
			throw new \Exception('Class not exists.');
		}
		$parser = new ClassParser($class, $forceNamespace);
		
		return $parser;
	}
	
	public function parserExtension($extensionName, $forceNamespace = FALSE) {
		if(!extension_loaded($extensionName)) {
			throw new \Exception($extensionName . ' extension not load.');
		}
		$ref = new \ReflectionExtension($extensionName);
		var_dump($ref->getClasses());
		var_dump($ref->getClassNames());
		var_dump($ref->getConstants());
		var_dump($ref->getDependencies());
		var_dump($ref->getFunctions());
		var_dump($ref->getINIEntries());
		var_dump($ref->getName());
		var_dump($ref->getVersion());
		var_dump($ref->info());
	}
}