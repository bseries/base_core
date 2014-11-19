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

use RuntimeException;
use lithium\core\Libraries;
use lithium\util\Validator;
use lithium\g11n\Message;

use base_core\extensions\cms\Settings;

use base_core\models\Addresses;

class VirtualUsers extends \base_core\models\Base {

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber' => [
			'models' => [
				'base_core\models\Users',
				'base_core\models\VirtualUsers'
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
		if (Libraries::get('billing_core')) {
			return $entity->name . '/' . $entity->number;
		}
		return $entity->name . '/' . $entity->id;
	}

	public static function init() {
		extract(Message::aliases());
		$model = static::_object();

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
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
	//
	// Can only be used if base_address is available. Address type field
	// availability depends on used libraries.
	public function address($entity, $type = 'billing') {
		if (!static::hasField($field = "{$type}_address")) {
			$message  = "User model has no field `{$filed}`. ";
			$message .= "You may need to require the ecommerce_core or billing_core library.";
			throw new RuntimeException($message);
		}

		if ($entity->$field) {
			// Return if directly attached.
			return $entity->$field;
		}
		if (!class_exists('Address')) {
			$message  = "The base_address library is not available. ";
			$message .= "Require it as a dependency to enable `Users::address()`.";
			throw new RuntimeException($message);
		}
		return Addresses::find('first', [
			'conditions' => [
				'id' => $entity->{"{$type}_address_id"}
			]
		]) ?: Addresses::create();
	}
}

VirtualUsers::init();

?>