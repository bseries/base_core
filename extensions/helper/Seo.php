<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

	// Returns the meta description for the page enclosed in tags.
	public function description() {
		if ($this->_description) {
			return sprintf('<meta name="description" content="%s">', $this->_description);
		}
	}
}

?>