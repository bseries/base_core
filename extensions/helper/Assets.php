<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace base_core\extensions\helper;

use Exception;
use base_core\extensions\cms\Settings;
use base_core\models\Assets as AssetsModel;
use lithium\core\Libraries;
use lithium\util\Inflector;

class Assets extends \lithium\template\Helper {

	public function image($path, array $options = []) {
		$path = $this->url($path);
		return $this->_context->html->image($path, $options);
	}

	public function style($path, array $options = []) {
		$defaults = ['type' => 'stylesheet', 'inline' => true];
		list($scope, $options) = $this->_options($defaults, $options);

		if (is_array($path)) {
			foreach ($path as $i => $item) {
				$item = $this->url($item, '.css');
				$path[$i] = $this->_context->html->style($item, $scope);
			}
			return ($scope['inline']) ? join("\n\t", $path) . "\n" : null;
		}
		$path = $this->url($path, '.css');
		return $this->_context->html->style($path, $options);
	}

	public function script($path, array $options = []) {
		$defaults = ['inline' => true];
		list($scope, $options) = $this->_options($defaults, $options);

		if (is_array($path)) {
			foreach ($path as $i => $item) {
				$item = $this->url($item, '.js');
				$path[$i] = $this->script($item, $scope);
			}
			return ($scope['inline']) ? join("\n\t", $path) . "\n" : null;
		}
		if (strpos($path, '://') === false) {
			$path = $this->url($path, '.js');
		}
		return $this->_context->html->script($path, $options);
	}

	public function url($path, $suffix = null) {
		if (strpos($path, '://') !== false) {
			return $path;
		}
		$version = PROJECT_VERSION_BUILD;
		return $this->base() . '/v:' . $version . $path . $suffix;
	}

	public function urls($pattern) {
		$fileBase = parse_url($this->base('file'), PHP_URL_PATH);
		$httpBase = $this->base();

		$results = glob($fileBase . $pattern);

		foreach ($results as &$result) {
			$result = str_replace($fileBase, $httpBase, $result);
		}
		return $results;
	}

	public function base($scheme = null) {
		return AssetsModel::base($scheme ?: $this->_context->request());
	}

	public function availableScripts($type, array $options = []) {
		$options += ['admin' => false];

		$scripts = [];

		if ($type == 'base') {
			// Load base js files in cms_* assets/js.
			// Filter out any non-cms libraries, then sort.
			$libraries = Libraries::get();
			$libraries = array_filter($libraries, function($a) {
				return preg_match('/^(base|cms|ecommerce|billing)_/', $a['name']) || $a['name'] === 'app';
			});
			uasort($libraries, function($a, $b) {
				// Keep app last...
				if ($a['name'] === 'app') {
					return 1;
				}
				if ($b['name'] === 'app') {
					return -1;
				}
				// ... and core first.
				if ($a['name'] === 'base_core') {
					return -1;
				}
				if ($b['name'] === 'base_core') {
					return 1;
				}
				return strcmp($a['name'], $b['name']);
			});

			// Load base files. When in admin context also
			// load all module base files, if in app context
			// do not rely on any module JS, load only app base.
			if (!$options['admin']) {
				// Load only app's base.js not anything else, when in app context.
				if ($script = $this->_script('app', 'base')) {
					$scripts[] = $script;
				}
			} else {
				foreach ($libraries as $name => $library) {
					if ($name == 'app' && $options['admin']) {
						// Do not load app base.js when in admin context.
						continue;
					}
					if ($script = $this->_script($name, 'base')) {
						$scripts[] = $script;
					}
				}
			}
		} elseif ($type == 'layout') {
			// Load corresponding layout script; when admin load it from _core
			// when in app load it from app.
			$library = $options['admin'] ? 'base_core' : 'app';
			$layout = $this->_context->_config['layout'];

			if ($script = $this->_script($library, "views/layouts/{$layout}")) {
				$scripts[] = $script;
			}
		} elseif ($type == 'view') {
			// Load corresponding view scripts automatically.
			$library = $this->_context->_config['library'];
			$controller = $this->_context->_config['controller'];
			$template = Inflector::camelize($this->_context->_config['template'], false);

			if ($script = $this->_script($library, "views/{$controller}/{$template}")) {
				$scripts[] = $script;
			}
		} elseif ($type == 'element') {

		}
		return $scripts;
	}

	protected function _script($library, $file) {
		$library = str_replace('_', '-', $library);
		$base = parse_url(AssetsModel::base('file'), PHP_URL_PATH);

		if (!is_dir($base)) {
			throw new Exception("Assets base directory `{$base}` does not exist.");
		}
		if (file_exists("{$base}/{$library}/js/{$file}.js")) {
			return "/{$library}/js/{$file}";
		}
	}
}

?>