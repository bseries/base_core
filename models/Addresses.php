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

namespace base_core\models;

use lithium\core\Environment;
use base_core\models\Countries;
use lithium\util\Validator;
use lithium\g11n\Message;

class Addresses extends \base_core\models\Base {

	use \base_core\models\UserTrait;

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp'
	];

	public static function init() {
		extract(Message::aliases());
		$model = static::_object();

		$model->validates['name'] = [
			[
				'nameOrCompany',
				'on' => ['create', 'update'],
				'message' => 'Bitte geben Sie einen Namen und/oder eine Firma an.'
			]
		];
		Validator::add('nameOrCompany', function($value, $format, $options) {
			return !empty($value) || !empty($options['values']['company']);
		});
		$model->validates['street'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'last' => true,
				'message' => $t('This field cannot be empty.')
			],
			[
				'streetNo',
				'on' => ['create', 'update'],
				'message' => $t('Missing street number.')
			],
		];
		Validator::add('streetNo', function($value, $format, $options) {
			return preg_match('/\s[0-9]/', $value);
		});

		$locale = Environment::get('locale');
		if (strpos($locale, '_') === false) {
			$locale = $locale . '_' . strtoupper($locale);
		}
		$model->validates['zip'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'last' => true,
				'message' => $t('This field cannot be empty.')
			],
			[
				'postalCode',
				'on' => ['create', 'update'],
				'format' => $locale,
				'message' => $t('The field is not correctly formatted.')
			],
		];
		$model->validates['city'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be empty.')
			],
		];
		$model->validates['country'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('A country must be selected.')
			],
		];
		$model->validates['phone'] = [
			[
				'phone',
				'on' => ['create', 'update'],
				'skipEmpty' => true,
				'message' => $t('The field is not correctly formatted.')
			],
		];
	}

	public function title($entity) {
		return $entity->format('oneline');
	}

	public static function findExact($data) {
		return static::find('first', [
			'conditions' => $data
		]);
	}

	public function copy($entity, $object, $prefix = null) {
		$skipFields = ['id', 'user_id', 'created', 'modified'];

		foreach ($entity->data() as $field => $value) {
			if (in_array($field, $skipFields)) {
				continue;
			}
			$field = $prefix ? $prefix . $field : $field;
			$object->{$field} = $value;
		}
		return $object;
	}

	public static function createFromPrefixed($prefix, array $data) {
		$item = [];

		foreach ($data as $field => $value) {
			// Also includes unprefixed virtual_user_id and user_id.
			if (strpos($field, 'user_') !== false) {
				$item[$field] = $value;
				continue;
			}
			if (strpos($field, $prefix) !== false) {
				$field = str_replace($prefix, '', $field);
				$item[$field] = $value;
				continue;
			}
		}
		return static::create($item);
	}

	public function format($entity, $type, $locale = null) {
		if (!$locale) {
			$locale = ($user = $entity->user()) ? $user->locale : Environment::get('locale');
		}

		if ($type == 'oneline') {
			$result = [];

			$result[] = $entity->company;
			$result[] = $entity->name;
			$result[] = $entity->street;
			$result[] = $entity->city;

			return implode(', ', array_filter($result));
		}
		if ($type == 'postal') {
			$result = [];

			$result[] = $entity->company;

			if ($entity->company) {
				$result[] = "– {$entity->name} –";
			} else {
				$result[] = $entity->name;
			}
			$result[] = $entity->street;
			$result[] = $entity->zip . ' ' . $entity->city;
			$result[] = Countries::find('first', [
				'conditions' => [
					'id' => $entity->country
				],
				'locale' => $locale
			])->name;
			return implode("\n", array_filter($result));
		}
	}
}

Addresses::init();

?>