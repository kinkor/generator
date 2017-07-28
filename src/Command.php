<?php
namespace Kinkor\Generator;

class Command {
	
	public static function run($argv) {
		unset($argv[0]);
		$argv = array_values($argv);
		$command = new Command();
		if(!$argv || $argv[0] == '--help') {
			$command->help();
		}
		try {
			$command->analysisParam(array_values($argv));
		} catch(\Exception $e) {
			echo 'Exception Message: '. $e->getMessage() . PHP_EOL;
			$command->help();
		}
	}
	
	public function analysisParam($argv) {
		if(count($argv) < 2) {
			throw new \Exception('Arguments miss');
		}
		$options = explode('=', $argv[0]);
		$path = $argv[1];
		switch($options[0]) {
			case '--extension':
				
				break;
			default:
				throw new \Exception('Options error');
		}
	}
	
	public function help($str = '') {
		switch($str) {
			default:
				print <<<EOF
Usage: generator options path
  
  path File output path.

Options:
  --extension=extension-name Generator code this extension.
  --help                     Print this usage information.
EOF;
				echo PHP_EOL;
		}
		exit;
	}
}