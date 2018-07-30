<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
		$version = PROJECT_VERSION;
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

	public function availableStyles($type, array $options = []) {
		return $this->_availableAssets('style', $type, $options);
	}

	public function availableScripts($type, array $options = []) {
		return $this->_availableAssets('script', $type, $options);
	}

	protected function _availableAssets($assetType, $viewType, array $options = []) {
		$options += ['admin' => false];

		$assets = [];

		if ($viewType == 'base') {
			// Load base files. When in admin context also load all module base files, if
			// in app context do not rely on any module JS/CSS, load only app base.
			if (!$options['admin']) {
				// Load only app's base.js not anything else, when in app context.
				if ($asset = $this->_asset($assetType, 'app', 'base')) {
					$assets[] = $asset;
				}
			} else {
				$libraries = $this->_libraries();

				// Styles must be loaded in reverse order, so that base styles are defined
				// first, than overrides take place.
				if ($assetType === 'style') {
					$libraries = array_reverse($libraries);
				}
				// Load base JS/CSS files in i.e. cms_* assets/{js,css}.
				foreach ($libraries as $name => $library) {
					if ($name == 'app' && $options['admin']) {
						// Do not load app base.js/base.css when in admin context.
						continue;
					}
					if ($asset = $this->_asset($assetType, $name, 'base')) {
						$assets[] = $asset;
					}
				}
			}
		} elseif ($viewType == 'layout') {
			// Load corresponding layout asset; when admin load it from _core when in app
			// load it from app.
			$library = $options['admin'] ? 'base_core' : 'app';
			$layout = Inflector::camelize($this->_context->_config['layout'], false);

			if ($asset = $this->_asset($assetType, $library, "views/layouts/{$layout}")) {
				$assets[] = $asset;
			}
		} elseif ($viewType == 'view') {
			// Load corresponding view assets automatically.
			$library = $this->_context->_config['library'];
			$controller = $this->_context->_config['controller'];
			$template = Inflector::camelize($this->_context->_config['template'], false);

			if ($asset = $this->_asset($assetType, $library, "views/{$controller}/{$template}")) {
				$assets[] = $asset;
			}
		} elseif ($viewType == 'element') {

		}
		return $assets;
	}

	// Returns custom sorted array of libraries.
	protected function _libraries() {
		$priorities = array_flip([
			'base',
			'cms',
			'billing',
			'ecommerce'
		]);
		$libraries = array_filter(Libraries::get(), function($l) {
			return preg_match('/^((base|cms|billing|ecommerce)_|app)/', $l['name']);
		});
		uasort($libraries, function($a, $b) use ($priorities) {
			// Keep app last...
			if ($a['name'] === 'app') {
				return 1;
			}
			if ($b['name'] === 'app') {
				return -1;
			}
			if ($a['name'] === $b['name']) {
				return 0;
			}

			preg_match('/^([a-z]+)_([a-z_]+)$/', $a['name'], $ma);
			preg_match('/^([a-z]+)_([a-z_]+)$/', $b['name'], $mb);

			if ($ma[2] === 'core' && $mb[2] === 'core') {
				if ($priorities[$ma[1]] > $priorities[$mb[1]]) {
					return -1;
				}
				if ($priorities[$ma[1]] < $priorities[$mb[1]]) {
					// billing_core after ecommere_core
					return 1;
				}
			}
			if ($ma[2] === 'core') {
				return 1;
			}
			if ($mb[2] === 'core') {
				return -1;
			}
			if ($priorities[$ma[1]] > $priorities[$mb[1]]) {
				return -1;
			}
			if ($priorities[$ma[1]] < $priorities[$mb[1]]) {
				// billing_core after ecommere_core
				return 1;
			}
			// cms_social after cms_banner
			return strcmp($a['name'], $b['name']);
		});
		return $libraries;
	}

	protected function _asset($assetType, $library, $file) {
		$library = str_replace('_', '-', $library);
		$base = parse_url(AssetsModel::base('file'), PHP_URL_PATH);

		if (!is_dir($base)) {
			throw new Exception("Assets base directory `{$base}` does not exist.");
		}
		$fragment = $assetType === 'script' ? 'js' : 'css';

		if (file_exists("{$base}/{$library}/{$fragment}/{$file}.{$fragment}")) {
			return "/{$library}/{$fragment}/{$file}";
		}
	}
}

?>