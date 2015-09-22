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

use base_core\extensions\cms\Settings;

class Seo extends \lithium\template\Helper {

	protected $_description = null;

	// Sets SEO information.
	//
	// Titles will sync with context's title so we keep information about
	// this type in the usual place. For all other types information is
	// stored in properties of this helper.
	public function set($type, $value) {
		if ($type === 'title') {
			return $this->_context->title($value);
		}
		return $this->{"_{$type}"} = $value;
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
	public function title($separator = ' – ') {
		if (!INSIDE_ADMIN && in_array($this->_context->request()->action, ['home', 'front'])) {
			$result = $this->_context->title();
		} else {
			$site = Settings::read('site');

			if (INSIDE_ADMIN) {
				$site['title'] = "Admin{$separator}{$site['title']}";
			}

			if ($title = $this->_context->title()) {
				$result = "{$title}{$separator}{$site['title']}";
			} else {
				$result = $site['title'];
			}
		}
		return sprintf('<title>%s</title>', $result);
	}

	// Returns the meta description for the page enclosed in tags.
	public function description() {
		if (!empty($this->_context->data()['seo']['description'])) {
			$message  = 'Setting the SEO description via `$this->set([...seo..])` is ';
			$message .= 'deprecated. Use the SEO helper instead: `$this->seo->set(\'description\', ...)`.';
			trigger_error($message, E_USER_DEPRECATED);

			$this->_description = $this->_context->data()['seo']['description'];
		}

		if ($this->_description) {
			return sprintf('<meta name="description" content="%s">', $this->_description);
		}
	}
}

?>