<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\helper;

/**
 * Nav Helper to generate navigation elements.
 */
class Nav extends \lithium\template\Helper {

	const COMPLETE_MATCH = 10;
	const PARTIAL_MATCH = 5;
	const PARTIAL_MISMATCH = -5;
	const COMPLETE_MISMATCH = -10;

	/**
	 * Holds a multi-dimensional array of navigation items. First keyed by section holding
	 * an array of items belonging to that section.
	 *
	 * @var array
	 */
	protected $_items = [];

	/**
	 * Generates a HTML element for accessibility purposes. Must be combined
	 * with CSS to achieve effect.
	 *
	 * @param string $to ID of element to skip to, defaults to `'content'`.
	 * @return string HTML
	 */
	public function skip($to = 'content') {
		$html  = '<p class="hide">';
		$html .= $this->_context->html->link('Skip navigation.', "#{$to}");
		$html .= '</p>';

		return $html;
	}

	/**
	 * Adds a navigation item to a section.
	 */
	public function add($section, $title, $url = null, array $options = []) {
		if (is_array($title)) {
			foreach($title as $item) {
				$this->add($section, $item['title'], $item['url'], (array) $url);
			}
			return null;
		}
		if (!$url) {
			$url = $title;
		}
		$default = [
			'escape' => true,
			'exclude' => false, // When `true` will not try to match this item.
			'active' => null,
			'id' => null,
			'class' => null,
			'title' => null,
			'rel' => null,
			'target' => null,
			'nested' => null
		];
		$options = array_merge($default, $options);
		$this->_items[$section][] = [
			'escape' => $options['escape'],
			'exclude' => $options['exclude'],
			'active' => $options['active'],
			'nested' => $options['nested'],
			'link' => [
				'rel' => $options['rel'],
				'target' => $options['target']
			],
			'title' => $title,
			'url' => $url,
			'id' => $options['id'],
			'class' => $options['class'],
			'_title' => $options['title'] // This obviously is a hack :)
		];
	}

	/**
	 * Generates a navigation for given section.
	 *
	 * @param string $section The navigation section.
	 * @param array $options Available options are:
	 *              - `'match'` _string_: Allows you to pick a matching algorithm
	 *                for this section. The algorithm determines which item will
	 *                be set active. Possible values are `'strict'`, `'loose'`, `'diff'`
	 *                and `'option'`. Defaults to `'option'`.
	 * @param array $items
	 * @return string HTML
	 */
	public function generate($section, $options = [], array $items = []) {
		$default = [
			'match' => 'option',
			'reset' => false,
			'class' => null,
			'tag' => 'nav',
			'itemTag' => null,
			'id' => null
		];
		$options += $default;
		$out = null;

		if (empty($items)) {
			if (!isset($this->_items[$section])) {
				return null;
			}
			$items = $this->_items[$section];
		}

		$active = ['key' => null, 'match' => null];

		foreach ($items as $key => &$item) {
			if ($item['exclude']) {
				continue;
			}
			$subject = $this->_context->url($item['url']);
			$object = $this->_context->request()->url;

			if ($options['match'] === 'option') {
				if ($item['active']) {
					$active = ['key' => $key, 'match' => true];
					break;
				}
				continue;
			}
			if ($options['match'] === 'diff') {
				$count = $this->_countDiffUrls($subject, $object);

				if ($count < $active['match'] || $active['match']) {
					$active = ['key' => $key, 'match' => $count];
					// Never break continue to find best match.
				}
				continue;
			}
			if ($options['match'] === 'loose') {
				$subject = parse_url($subject, PHP_URL_PATH);
				$object = parse_url($object, PHP_URL_PATH);

				$requireMatch = self::PARTIAL_MATCH;
			} else { // strict
				$requireMatch = self::COMPLETE_MATCH;
			}
			$match = $this->_matchContain($subject, $object);

			if ($match >= $requireMatch || ($match > $active['match'] && $active['match'])) {
				$active = ['key' => $key, 'match' => $match];
				// Never break continue to find best match.
			}
		}
		unset($item);

		if (isset($active['key'])) {
			$items[$active['key']]['active'] = true;
		}

		/* Format */
		$out = null;
		foreach ($items as $item) {
			$linkOptions = array_filter([
				'escape' => $item['escape'],
				'title' => $item['_title']
			], function($v) {
				return is_bool($v) || !empty($v);
			}) + array_filter($item['link']);

			if ($options['itemTag']) {
				$attributes = array_filter([
					'class' => $item['class'],
					'id' => $item['id']
				]);

				if ($item['active']) {
					if (isset($attributes['class'])) {
						$attributes['class'] .= ' active';
					} else {
						$attributes['class'] = 'active';
					}
				}
				$attributes = $this->_attributes($attributes);
				$out .= "<{$options['itemTag']}{$attributes}>";
				$out .= $this->_context->html->link($item['title'], $item['url'], $linkOptions);

				if ($item['nested']) {
					$out .= $this->generate($item['nested'], ['class' => $options['class'] . '-nested'] + $options);
				}
				$out .= "</{$options['itemTag']}>";
			} else {
				$attributes = [
					'escape' => $item['escape']
				] + $linkOptions;

				if ($item['class']) {
					$attributes['class'] = $item['class'];
				}

				if ($item['active']) {
					if (isset($attributes['class'])) {
						$attributes['class'] .= ' active';
					} else {
						$attributes['class'] = 'active';
					}
				}
				$out .= $this->_context->html->link($item['title'], $item['url'], $attributes);

				if ($item['nested']) {
					$out .= $this->generate($item['nested'], $options);
				}
			}
		}

		if ($options['reset']) {
			unset($this->_items[$section]);
		}

		if ($options['tag']) {
			$attributes = $this->_attributes(array_filter([
				'class' => $options['class'],
				'id' => $options['id']
			]));
			$html  = "<{$options['tag']}{$attributes}>";
			$html .= $out;
			$html .= "</{$options['tag']}>";

			return $html;
		} else {
			return $out;
		}
	}

	public function instant($title, $url, $options = []) {
		$options = array_merge(['tag' => false, 'itemTag' => false], $options);

		$item = [
			'title' => $title,
			'url' => $url,
			'class' => null
		];
		return $this->generate(null, $options, [$item]);
	}

	protected function _matchContain($subject, $object) {
		if (empty($subject)) {
			return self::COMPLETE_MISMATCH;
		}
		if ($subject === $object) {
			return self::COMPLETE_MATCH;
		}

		$matchPosition = strpos($object, $subject);

		if ($matchPosition === 0) {
			return self::PARTIAL_MATCH;
		}
		if ($matchPosition === false) {
			return self::COMPLETE_MISMATCH;
		}
		return self::PARTIAL_MISMATCH;
	}

	protected function _matchDiff($subject, $object) {
		return count(array_diff_assoc(explode('/', $subject), explode('/', $object)));
	}
}

?>