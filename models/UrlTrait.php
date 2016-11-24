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

namespace base_core\models;

use Exception;
use lithium\analysis\Logger;

// Class where this trait is used must provide a static `base()` method.
trait UrlTrait {

	// Provided by SchemeTrait.
	abstract protected function _negotiateScheme($scheme);

	public function scheme($entity) {
		return parse_url($entity->url, PHP_URL_SCHEME);
	}

	// Supports "host-less" URLs for non HTTP schemes.
	public function path($entity, array $options = []) {
		$url = parse_url($entity->url);

		$options += [
			'withHost' => $url['scheme'] != 'http' && $url['scheme'] != 'https'
		];
		$path = [];

		if (isset($url['host']) && $options['withHost']) {
			$path[] = $url['host'];
		}
		if (isset($url['path'])) {
			$path[] = ltrim($url['path'], '/');
		}
		return implode('/', $path);
	}

	// Assumes when requesting http, https would be ok, too. **Always
	// returns absolute URLs.** $targetScheme can either be a string or an
	// \lithium\net\http\Request object, to auto negotatiate the best HTTP
	// scheme. This works similar to SchemeTrait's base() method.
	public function url($entity, $targetScheme) {
		$sourceScheme = parse_url($entity->url, PHP_URL_SCHEME);
		$targetScheme = static::_negotiateScheme($targetScheme);

		if ($targetScheme === $sourceScheme) {
			return static::absoluteUrl($entity->url);
		}
		if ($targetScheme === 'http' && $sourceScheme === 'https') {
			// Allow HTTPS for HTTP.
			return static::absoluteUrl($entity->url);
		}

		// Just absolute URLs can be transitioned between schemes. Fails when we
		// can't make the source URL absolute?

		// Transition to new scheme by exchanging base.
		if (!$sourceBase = static::base($sourceScheme)) {
			$message  = "Cannot transition URL `{$entity->url}` from scheme `{$sourceScheme}`;";
			$message .= " no base found for scheme `{$sourceScheme}`.";
			throw new Exception($message);
		}
		if (!$targetBase = static::base($targetScheme)) {
			$message  = "Cannot transition URL `{$entity->url}` to scheme `{$targetScheme}`;";
			$message .= " no base found for scheme `{$targetScheme}`.";
			throw new Exception($message);
		}
		return str_replace($sourceBase, $targetBase, static::absoluteUrl($entity->url));
	}

	// Ensures an URL is absolute.
	public static function absoluteUrl($url) {
		$scheme = parse_url($url, PHP_URL_SCHEME);
		// Note: parse_url only partially works with relative URLs.

		if ($url[strlen($scheme . '://')] == '/') {
			return $url; // already absolute
		}
		if (!$base = static::base($scheme)) {
			throw new Exception("Cannot make URL `{$url}` absolute; no base found for scheme `{$scheme}`.");
		}
		return str_replace($scheme . '://', $base . '/', $url);
	}

	// Ensures an URL is relative.
	public static function relativeUrl($url) {
		$scheme = parse_url($url, PHP_URL_SCHEME);
		$path   = parse_url($url, PHP_URL_PATH);

		if ($path[0] != '/') {
			return $url; // already relative
		}
		if (!$base = static::base($scheme)) {
			throw new Exception("Cannot make URL `{$url}` relative; no base found for scheme `{$scheme}`.");
		}
		return str_replace($base . '/', $scheme . '://', $url);
	}

	// By default doesn't do file_exists checks.
	// @fixme Re-factor this into Media_Util::generatePath()
	protected static function _uniqueUrl($base, $extension, array $options = []) {
		$options += ['exists' => false];

		$chars = 'abcdef0123456789';
		$length = 8;

		// Birthday problem: Likelihood of collision with 1M strings is 0.18%.
		// Prevent collisions. If this happens too "often" expand charset first.
		do {
			// Generate a random string for each round.
			$random = '';
			while (strlen($random) < $length) {
				$random .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}
			$path = substr($random, 0, 2) . '/' . substr($random, 2);

			if (!empty($extension)) {
				$path .= '.' . $extension;
	 		}
		} while (!$options['exists'] || file_exists($base . '/' . $path));

		return $base . '/' . $path;
	}

	// Delete only files that are local and within base.
	public function deleteUrl($entity) {
		$url = static::absoluteUrl($entity->url);

		if (strpos($url, static::base('file')) === false) {
			Logger::warning("Cannot delete URL `{$url}`; is not within base.");
			return false;
		}
		if (file_exists($url) && !unlink($url)) {
			return false;
		}
		return true;
	}
}

?>