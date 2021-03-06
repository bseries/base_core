<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\helper;

use base_core\base\Sites;

class Seo extends \lithium\template\Helper {

	protected $_description = null;

	// Sets SEO information.
	//
	// Titles will sync with context's title so we keep information about
	// this type in the usual place. For all other types information is
	// stored in properties of this helper.
	//
	// Usage:
	// ```
	// $this->seo->set('description', 'foobar');
	// $this->seo->set(['title' => 'foo', 'description' => 'bar']);
	// ```
	public function set($type, $value = null) {
		if (is_array($type)) {
			foreach ($type as $t => $v) {
				$this->set($t, $v);
			}
			return;
		}
		if ($type === 'title') {
			$this->_context->title($value);
		} else {
			$this->{"_{$type}"} = $value;
		}
	}

	// Returns page title with <title> tag.
	//
	// Will heuristically try to determine which pages are
	// landing pages (i.e. those named home or front) and
	// return the title as set without any additions.
	//
	// For any other pages will append the site's title
	// as per the `site.title` settings.
	//
	public function title(array $options = []) {
		$options += [
			'separator' => ' – ',
			'standalone' => 'auto',
			'admin' => 'auto',
			'site' => Sites::current($this->_context->request())->title() ?: '',
			'reverse' => true
		];
		if ($options['standalone'] === 'auto')  {
			$options['standalone'] = !INSIDE_ADMIN && in_array(
				$this->_context->request()->action, ['home', 'front']
			);
		}
		if ($options['admin'] === 'auto')  {
			$options['admin'] = INSIDE_ADMIN;
		}

		if ($options['standalone']) {
			$result = [$this->_context->title()];
		} else {
			if ($options['admin']) {
				$result[] = 'Admin';
			}
			if ($options['site']) {
				$result[] = $options['site'];
			}
			if ($title = $this->_context->title()) {
				$result[] = $title;
			}
		}

		if ($options['reverse']) {
			$result = array_reverse($result);
		}
		return sprintf('<title>%s</title>', implode($options['separator'], $result));
	}

	// Returns the meta description for the page by default enclosed in tags.
	// You can return the pure description by passing an optional parameter $raw.
	public function description($raw = false) {
		if (!$this->_description) {
			return null;
		}
		if ($raw) {
			return $this->_description;
		}
		return sprintf('<meta name="description" content="%s">', $this->_description);
	}
}

?>