<?php

namespace Phpm;

	class System {

		private static $instance;
		protected $packageSet = false;
		protected $packageSetPath = false;
		protected $doNotLoad = false;

		private function __construct() {

			$this->loadRCFile();
			if ($this->doNotLoad) {
				return;
			}

			if (empty($this->packageSet)) {
				$this->definePackageSet();
			}

			$this->definePackageSetPath();

		}

		static function initialize() {

			if (!System::initialized()) {
				System::$instance = new System;
			}

			return System::$instance;

		}

		static function initialized() {
			return !empty(System::$instance);
		}

		function __get($name) {
			return $this->$name;
		}

		protected function loadRCFile() {

			if (!file_exists(".phpmrc")) {
				return;
			}

			$rc = file(".phpmrc", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($rc as $line) {

				$line = trim($line);
				if ($line[0] == "#") {
					continue;
				}

				$config = explode("=", $line);
				$key = trim($config[0]);
				$value = trim($config[1]);
				if ($key == "no_load") {

					if ($value == "1" || $value == "true") {
						$this->doNotLoad = true;
					}

				}
				else if ($key == "package_set") {
					$this->packageSet = $value;
				}

			}

		}

		protected function definePackageSet() {

			if (($this->packageSet = getenv("PHPMP_HOME")) === false) {
				$this->packageSet = "default";
			}

		}

		protected function definePackageSetPath() {

			$packageSetPath = __DIR__."/../packages/".$this->packageSet."/packages";
			if (($this->packageSetPath = realpath($packageSetPath)) === false) {
				return;
			}

			$this->packageSetPath = rtrim($this->packageSetPath, "/")."/";

		}

		function loadClass($class) {

			$class = str_replace("\\", "/", $class);

			$matches = [];
			$package = lcfirst($class);
			preg_match_all("/[^\/][A-Z]/", $package, $matches);
			foreach ($matches[0] as $match) {
				$package = str_replace($match, $match[0]."_".strtolower($match[1]), $package);
			}

			$package = strtolower($package);

			if (strpos($class, "Phpm") === 0) {
				$this->loadCoreClass($package);
			}
			else {
				$this->loadPackageClass($package);
			}

		}

		protected function loadCoreClass($class) {

			$path = substr($class, 4).".php";
			$class = realpath(__DIR__.$path);
			if (empty($class)) {
				$class = realpath(__DIR__."/../src".$path);
			}
			require_once($class);
			return;

		}

		protected function loadPackageClass($package) {

			$components = explode("/", $package);
			$packageHome = $this->packageSetPath.$components[0];

			if (file_exists($packageHome."/autoload.php")) {
				require_once($packageHome."/autoload.php");
			}
			else if (file_exists($packageHome."/src/".implode("/", array_slice($components, 1)).".php")) {
				require_once($packageHome."/src/".implode("/", array_slice($components, 1)).".php");
			}

		}

	}

?>