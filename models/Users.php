<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
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

namespace base_core\models;

use Exception;
use RuntimeException;
use base_address\models\Addresses;
use base_core\extensions\cms\Settings;
use base_core\models\Assets;
use base_core\security\Gate;
use billing_core\billing\ClientGroups;
use billing_core\billing\TaxTypes;
use lithium\analysis\Logger;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\security\Password;
use lithium\util\String;
use lithium\util\Validator;

class Users extends \base_core\models\Base {

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Sluggable',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Uuid',
		'base_core\extensions\data\behavior\ReferenceNumber' => [
			'models' => [
				'base_core\models\Users'
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
		'role' => [] // Dynamicall added from Gate, in Users::init().
	];

	public static function init() {
		extract(Message::aliases());

		$model = static::_object();

		static::$enum['role'] = array_keys(Gate::roles());

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('user.number')
		);

		$model->validates['password'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['passwordInit', 'passwordChange'],
				'required' => true,
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
		];
		$model->validates['password_repeat'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['passwordRepeat'],
				'required' => true,
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
			'repeat' => [
				'passwordRepeat',
				'on' => ['passwordRepeat'],
				'required' => true,
				'message' => $t('The passwords are not identical.', ['scope' => 'base_core'])
			]
		];
		Validator::add('passwordRepeat', function($value, $format, $options) {
			return Password::check($value, $options['values']['password']);
		});

		$model->validates['answer'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['answerInit'],
				'required' => true,
				'message' => $t('This field cannot be empty.', ['scope' => 'base_core'])
			],
		];

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

		// Override default finder to switch between number/name order.
		$isFinancial = (boolean) Libraries::get('billing_core');
		static::finder('list', function($self, $params, $chain) use ($isFinancial) {
			$result = [];

			$hasLots = static::find('count') > 100;

			$params['options']['order'] = ['name' => 'ASC'];
			$params['options']['fields'] = ['id', 'name'];

			if ($isFinancial) {
				$params['options']['order'] = ['number' => $hasLots ? 'DESC' : 'ASC'];
				$params['options']['fields'][] = ['number'];
			}

			if ($hasLots) {
				$params['options']['order'] = ['year' => 'DESC'] + $params['options']['order'];
				$params['options']['fields'][] = 'YEAR(created) AS year';
			}

			// FIXME Group by common prefix.
			// http://stackoverflow.com/questions/1336207/finding-common-prefix-of-array-of-strings
			foreach ($chain->next($self, $params, $chain) as $entity) {
				if ($hasLots) {
					$result[$entity->year][$entity->id] = $entity->title();
				} else {
					$result[$entity->id] = $entity->title();
				}
			}
			return $result;
		});
	}

	// For displaying and listing users.
	// find('list') will pick this up.
	public function title($entity) {
		static $financial = null;

		if ($financial === null) {
			$financial = (boolean) Libraries::get('billing_core');
		}
		if ($financial) {
			return $entity->number . ' / ' . $entity->name;
		}
		return $entity->name;
	}

	/* Security */

	// Checks if the entity will be treated as if it was locked. I.e. because
	// the password is expired etc. Might be used to verify if user
	// has 2FA enabled.
	public function mustLock($entity) {
		$fields = [
			'uuid',
			'email',
			'password'
		];
		foreach ($fields as $field) {
			if (empty($entity->{$field})) {
				return true;
			}
		}
		if ($entity->reset_token) {
			return true;
		}
		if (!$entity->is_active) {
			return true;
		}
		return false;
	}

	// Generates a random (pronounceable) plaintext password.
	public static function generatePassword($length = 12, $alphabet = 0) {
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

	public static function hashAnswer($answer) {
		return String::hash($answer, ['type' => 'sha512']);
	}

	public static function checkAnswer($answer, $hash) {
		return String::hash($answer, ['type' => 'sha512']) === $hash;
	}

	// Performs password reset process and returns the
	// reset token which should be mailed to the user.
	//
	// https://www.owasp.org/index.php/Forgot_Password_Cheat_Sheet
	public static function resetPasswordRequest(array $conditions) {
		$message = 'Receiving password reset request...' ;
		Logger::debug($message);

		$conditions += [
			'email' => null,
			'answer' => null,
			'is_active' => true,
			'is_locked' => false
		];
		foreach ($conditions as $key => &$value) {
			// Prevent searching for empty values and getting more results
			// then intented. Also prevent human error.
			if (empty($value)) {
				throw new Exception("Constraint `{$key}` is empty.");
			}
			// The answer key is hashed in order to not leak information about the password.
			// Users might give a hint to their actual password.
			if ($key === 'answer') {
				$value = static::hashAnswer($value);
			}
		}
		unset($value);

		$item = static::find('first', [
			'conditions' => $conditions,
			'fields' => ['id']
		]);
		if (!$item) {
			$message  = 'Password reset request failed! ';
			$message .= 'With: ' . var_export($conditions, true);
			Logger::debug($message);

			return false;
		}
		$result = $item->save([
			'reset_token' => static::generateToken(),
			'is_locked' => true // Lock user account as per OWASP
		], [
			'whitelist' => ['reset_token', 'is_locked'],
			'validate' => false
		]);
		if (!$result) {
			throw new Exception('Failed to save.');
		}
		$message  = 'Password reset request succeeded; ';
		$message .= "generated reset token `{$item->reset_token}` and locked user `{$item->id}`. ";
		$message .= 'With: ' . var_export($conditions, true);
		Logger::debug($message);

		// Limit the set of returned data, so that when used in email templates
		// one isn't tempted to use secret fields.
		return static::find('first', [
			'conditions' => ['id' => $item->id],
			'fields' => ['id', 'name', 'email', 'reset_token', 'locale']
		]);
	}

	public static function resetPasswordAccept($passwordNewPlaintext, array $conditions) {
		$message = 'Receiving password reset accceptance...' ;
		Logger::debug($message);

		if (empty($passwordNewPlaintext)) {
			throw new Exception('$passwordNewPlaintext is empty.');
		}
		$conditions += [
			'email' => null,
			'reset_token' => null,
			'is_active' => true,
			'is_locked' => true
		];
		foreach ($conditions as $key => $value) {
			if (empty($value)) {
				throw new Exception("Constraint `{$key}` is empty.");
			}
		}

		$item = static::find('first', [
			'conditions' => $conditions,
			'fields' => ['id']
		]);
		if (!$item) {
			$message  = 'Password reset acceptance failed! ';
			$message .= 'With: ' . var_export($conditions, true);
			Logger::debug($message);

			return false;
		}
		$result = $item->save([
			'reset_token' => null, // Reset token to null.
			'is_locked' => false, // Reactivate account.
			'password' => static::hashPassword($passwordNewPlaintext),
		], [
			'whitelist' => ['reset_token', 'is_locked', 'password'],
			'validate' => false
		]);
		if (!$result) {
			throw new Exception('Failed to accept password request.');
		}
		$message  = 'Password reset accepted; ';
		$message .= "generated new password, reset token and unlocked user `{$item->id}`. ";
		$message .= 'With: ' . var_export($conditions, true);
		Logger::debug($message);

		// Limit the set of returned data, so that when used in email templates
		// one isn't tempted to use secret fields.
		return static::find('first', [
			'conditions' => ['id' => $item->id],
			'fields' => ['id', 'name', 'email', 'locale']
		]);
	}

	/* Address */

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
		]) ?: Addresses::create([
			'user_id' => $entity->id,
			'country' => $entity->country
		]);
	}

	/* Billing */

	public function taxType($entity) {
		return TaxTypes::registry($entity->tax_type);
	}

	public function clientGroup($entity) {
		return ClientGroups::registry(true)->first(function($item) use ($entity) {
			return $item->conditions($entity);
		});
	}
}

Users::init();

?>