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
use lithium\security\Password;
use lithium\g11n\Message;

class Users extends \cms_core\models\Base {

	protected $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public static $enum = [
		'role' => [
			'admin',
			'user'
		]
	];

	public static function init() {
		extract(Message::aliases());

		$model = static::_object();

		$model->validates['password'] = [
			[
				'notEmpty',
				'on' => ['create', 'passwordChange', 'passwordInit'],
				'message' => $t('This field cannot be empty.')
			],
		];
		$model->validates['password_repeat'] = [
			[
				'notEmpty',
				'on' => ['create'],
				'message' => $t('This field cannot be empty.')
			],
			[
				'passwordRepeat',
				'on' => ['create', 'passwordChange', 'passwordInit'],
				'message' => $t('The passwords are not identical.')
			]
		];
		Validator::add('passwordRepeat', function($value, $format, $options) {
			return Password::check($value, $options['values']['password']);
		});

		$model->validates['name'] = [
			[
				'notEmpty',
				'on' => ['create'],
				'message' => $t('This field cannot be empty.')
			]
		];

		$model->validates['email'] = [
			[
				'notEmpty',
				'on' => ['create'],
				'message' => $t('This field cannot be empty.')
			],
			[
				'email',
				'on' => ['create'],
				'message' => $t('Invalid e–mail.')
			],
			[
				'isUnique',
				'on' => ['create', 'update'],
				'message' => $t('The e–mail is already in use.')
			]
		];
		Validator::add('isUnique', function($value, $format, $options) {
			$conditions = [
				$options['field'] => $value
			];
			if (isset($options['values']['id'])) {
				$conditions['id'] = '!=' . $options['values']['id'];
			}
			return !Users::find('count', compact('conditions'));
		});
	}

	public function activate($entity) {
		$entity->is_active = true;
		return $entity->save(null, ['validate' => false, 'whitelist' => ['is_active']]);
	}

	public function deactivate($entity) {
		$entity->is_active = false;
		return $entity->save(null, ['validate' => false, 'whitelist' => ['is_active']]);
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

	// Force to use blowfish with 10 iterations.
	// This leads to hashed-password length of 60 characters.
	public static function hashPassword($plaintext, $hash = null) {
		return Password::hash($plaintext, $hash ?: Password::salt('bf', 10));
	}

	public static function checkPassword($plaintext, $hash) {
		return Password::check($plaintext, $hash);
	}
}

Users::init();

?>