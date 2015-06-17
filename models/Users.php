<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\models;

use Exception;
use RuntimeException;
use lithium\core\Libraries;
use lithium\util\Validator;
use lithium\security\Password;
use lithium\g11n\Message;

use base_core\extensions\cms\Settings;
use base_core\models\Assets;
use base_core\security\Gate;

use base_address\models\Addresses;

class Users extends \base_core\models\Base {

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\ReferenceNumber' => [
			'models' => [
				'base_core\models\Users',
				'base_core\models\VirtualUsers'
			]
		],
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'number',
				'name',
				'role',
				'email'
			]
		]
	];

	public static $enum = [
		'role' => [] // Dynamicall added to in bootstrap/access.php
	];

	public static function init() {
		extract(Message::aliases());

		$model = static::_object();

		static::$enum['roles'] = array_keys(Gate::roles());

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('user.number')
		);

		$model->validates['password'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['create', 'passwordChange', 'passwordInit'],
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
		];
		$model->validates['password_repeat'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['create'],
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
			'repeat' => [
				'passwordRepeat',
				'on' => ['create', 'passwordChange', 'passwordInit'],
				'message' => $t('The passwords are not identical.', ['scope' => 'base_core'])
			]
		];
		Validator::add('passwordRepeat', function($value, $format, $options) {
			return Password::check($value, $options['values']['password']);
		});

		$model->validates['name'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['create', 'update'],
				'last' => true,
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			]
		];
		$model->validates['email'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['create', 'update'],
				'last' => true,
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
			'email' => [
				'email',
				'deep' => true,
				'on' => ['create', 'update'],
				'message' => $t('Invalid e–mail.', ['scope' => 'base_core'])
			],
			'isUnique' => [
				'isUnique',
				'on' => ['create', 'update'],
				'message' => $t('The e–mail is already in use.', ['scope' => 'base_core'])
			]
		];
		Validator::add('isUnique', function($value, $format, $options) {
			$conditions = [
				$options['field'] => $value
			];
			if (!empty($options['values']['id'])) {
				$conditions['id'] = ['!=' => $options['values']['id']];
			}
			return !Users::find('count', compact('conditions'));
		});
	}

	public function title($entity) {
		if (Libraries::get('billing_core')) {
			return $entity->name . '/' . $entity->number;
		}
		return $entity->name;
	}

	public function isVirtual() {
		return false;
	}

	// Generates a random (pronounceable) plaintext password.
	public static function generatePassword($length = 8, $alphabet = 0) {
		// Alphabets in descending order of complexity.
		$alphabets = [
			// The most simple set without any special characters.
			'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ02345679',

			// This is the standard and conservative PPP set of 64 characters. It was
			// chosen to remove characters that might be confused with one another. Using
			// 4-characters per passcode, 16,777,216 passcodes are possible for very good
			// one time password security.
			//
			// Source: https://www.grc.com/ppp.htm
			'!#%+23456789:=?@ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz',

			// This is a much more "visually aggressive" (somewhat more interesting and
			// certainly much stronger) 88-character alphabet which supports the
			// generation.
			//
			// Source: https://www.grc.com/ppp.htm
			'!"#$%&\'()*+,-./23456789:;<=>?@ABCDEFGHJKLMNOPRSTUVWXYZ[\]^_abcdefghijkmnopqrstuvwxyz{|}~'
		];

		$chars = $alphabets[$alphabet];
		$password = '';

		while (strlen($password) < $length) {
			$password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $password;
	}

	public static function generateToken($length = 16) {
		$result = String::random(max($length * 2), 32); // At least 2x the length randomness, or 32.
		$result = String::hash($random); // Use strong hashing algo implictly by default.
		$result = substr($result, 0, $length); // Limit to length.

		return $result;
	}

	// Force to use blowfish with 10 iterations.
	// This leads to hashed-password length of 60 characters.
	public static function hashPassword($plaintext, $hash = null) {
		return Password::hash($plaintext, $hash ?: Password::salt('bf', 10));
	}

	public static function checkPassword($plaintext, $hash) {
		return Password::check($plaintext, $hash);
	}

	// Performs password reset process and returns the
	// reset token which should be mailed to the user.
	// On success returns [$token, $user].
	public static function resetPasswordRequest($email, array $constraints = []) {
		if (empty($email)) {
			throw new Exception('$email is empty');
		}
		$item = static::find('first', [
			'conditions' => [
				'email' => $email
			] + $contraints
		]);
		if (!$item) {
			return [false, null];
		}
		$result = $item->save([
			'token' => static::generateToken()
		], [
			'whitelist' => ['token'],
			'validate' => false
		]);
		if (!$result) {
			throw new Exception('Failed to save token.');
		}
		return [$item->token, $item];
	}

	// On success returns [$passwordNewPlaintext, $user].
	public static function resetPasswordAccept($email, $token, $passwordNewPlaintext, array $constraints = []) {
		if (empty($email) || empty($token) || empty($passwordNewPlaintext)) {
			throw new Exception('$email, $token and/or $passwordNewPlaintext is/are empty');
		}
		$item = static::find('first', [
			'conditions' => [
				'email' => $email,
				'token' => $token
			] + $contraints
		]);
		if (!$item) {
			return [false, null];
		}
		$result = $item->save([
			'token' => static::generateToken()
		], [
			'whitelist' => ['token'],
			'validate' => false
		]);
		if (!$result) {
			throw new Exception('Failed to save token.');
		}
		return [$item->token, $item];
	}

	// Will always return a address object, even if none is
	// associated with this user.
	//
	// Can only be used if base_address is available. Address type field
	// availability depends on used libraries.
	public function address($entity, $type = 'billing') {
		if (!static::hasField("{$type}_address_id")) {
			$message  = "User model has no field `{$type}_address_id`. ";
			$message .= "You may need to require the ecommerce_core or billing_core library.";
			throw new RuntimeException($message);
		}

		if ($entity->{"{$type}_address"}) {
			// Return if directly attached.
			return $entity->{"{$type}_address"};
		}
		if (!Libraries::get('base_address')) {
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

Users::init();

?>