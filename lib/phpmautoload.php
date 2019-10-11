<?php

	spl_autoload_register(function($class) {

		require_once __DIR__."/system.php";
		$phpm = Phpm\System::initialize();

		if (!$phpm->doNotLoad) {
			$phpm->loadClass($class);
		}

	});

	function register_testsuites_autoload($class) {

		$class = trim(str_replace("\\", "/", $class), "/");

		$matches = [];
		$package = lcfirst($class/*$components[0]*/);
		preg_match_all("/[^\/][A-Z]/", $package, $matches);
		foreach ($matches[0] as $match) {
			$package = str_replace($match, $match[0]."_".strtolower($match[1]), $package);
		}

		$package = strtolower($package);

		if (($package_set_home = getenv("PHPMP_HOME")) === false) {
			$package_set_home = realpath(__DIR__."/../packages/default/packages");
		}
		$package_set_home = rtrim($package_set_home, "/")."/";

		$test_suite = $package_set_home.$package;

		if (file_exists($test_suite.".php")) {
			require_once($test_suite.".php");
		}

	}

?>