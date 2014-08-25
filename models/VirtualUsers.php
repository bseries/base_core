<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_core\models;

use lithium\util\Validator;
use lithium\g11n\Message;
use cms_core\models\Addresses;
use billing_core\models\TaxZones;
use cms_core\extensions\cms\Settings;
use cms_core\extensions\cms\Features;

class VirtualUsers extends \cms_core\models\Base {

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\ReferenceNumber' => [
			'models' => [
				'cms_core\models\Users',
				'cms_core\models\VirtualUsers'
			]
		]
	];

	public static $enum = [
		'role' => [
			'admin',
			'user',
			'merchant',
			'customer'
		]
	];

	public function title($entity) {
		if (Features::enabled('useBilling')) {
			return $entity->name . '/' . $entity->number;
		}
		return $entity->name . '/' . $entity->id;
	}

	public static function init() {
		extract(Message::aliases());
		$model = static::_object();

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('user.number')
		);

		$model->validates['email'] = [
			[
				'notEmpty',
				'on' => ['addEmail'],
				'message' => $t('This field cannot be empty.'),
				'last' => true
			],
			[
				'email',
				'on' => ['addEmail'],
				'deep' => true,
				'message' => $t('Invalid e–mail.')
			]
		];

	}

	public function isVirtual() {
		return true;
	}

	// Will always return a address object, even if none is
	// associated with this user.
	public function address($entity, $type = 'billing') {
		$field = "{$type}_address";
		if ($entity->$field) {
			return $entity->$field;
		}

		$field = "{$type}_address_id";
		return Addresses::find('first', [
			'conditions' => [
				'id' => $entity->$field
			]
		]) ?: Addresses::create();
	}

	public function taxZone($entity) {
		return TaxZones::generate(
			($address = $entity->address('billing')) ? $address->country : null,
			$entity->vat_reg_no,
			$entity->locale
		);
	}
}

VirtualUsers::init();

?>