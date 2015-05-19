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

use base_media\models\Media;

class Editor extends \lithium\template\Helper {

	// Parses HTML generated with
	public function parse($html, array $options = []) {
		$options += [
			'mediaVersion' => 'fix0'
		];
		$regex = '#(<img\s+data-media-id="([0-9]+)".*?>)#i';

		if (!preg_match_all($regex, $html, $matches, PREG_SET_ORDER)) {
			return $html;
		}

		foreach ($matches as $match) {
			$medium = Media::find('first', [
				'conditions' => [
					'id' => $match[2]
				]
			]);
			if (!$medium) {
				continue;
			}
			$replace = $this->_context->media->image(
				$medium->version($options['mediaVersion'])
			);
			$html = str_replace($match[0], $replace, $html);
		}
		return $html;
	}
}

?>