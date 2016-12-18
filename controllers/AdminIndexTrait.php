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

namespace base_core\controllers;

use Zend\Paginator\Adapter\ArrayAdapter;
use base_core\base\Sites;
use Zend\Paginator\Paginator;
use base_core\extensions\cms\Settings;
use base_core\security\Gate;
use lithium\util\Set;

trait AdminIndexTrait {

	public function admin_index() {
		$model = $this->_model;
		$model::meta(); // Hack to ensure model is initialized and its behaviors, too.

		$query = [
			'conditions' => [],
			'with' => [],
			'order' => [],
			'page' => $this->request->page ?: 1,
			'limit' => 25
		];

		// Show only owner's records, if not admin.
		if ($model::hasBehavior('Ownable') && !Gate::checkRight('owner')) {
			$query['conditions']['owner_id'] = Gate::user(true, 'id');
		}

		if ($model::hasBehavior('Searchable')) {
			$query = $model::searchQuery(
				// UTF-8 chars must be decoded.
				urldecode($this->request->filter),
				$query
			);
		}

		$query = $this->_order($model, $query);

		$data = $this->_all($model, $query);
		$paginator = $this->_paginator($model, $query);

		$useOwner = Settings::read('security.checkOwner') && Gate::checkRight('owner');
		if ($useSites = Settings::read('useSites')) {
			$sites = Sites::enum();
		}

		return compact('data', 'paginator', 'useOwner', 'useSites', 'sites') + $this->_selects();
	}

	protected function _all($model, array $query) {
		return $model::find('all', $query);
	}

	protected function _paginate($model, array $query) {
		return $model::find('count', $query);
	}

	// Handle pagination.
	protected function _paginator($model, array $query) {
		$itemsPerPage = $query['limit'];
		$page = $query['page'];

		Paginator::setDefaultItemCountPerPage($itemsPerPage);
		Paginator::setDefaultScrollingStyle('Sliding');

		unset($query['page']);
		unset($query['limit']);
		unset($query['order']); // Optimize.
		$count = $this->_paginate($model, $query);

		$paginator = new Paginator(new ArrayAdapter(
			range(1, $count)
		));
		$paginator->setCurrentPageNumber($page);
		$paginator->setCacheEnabled(false);

		return $paginator;
	}


	protected function _order($model, array $query) {
		// Normalize order field and direction.
		// We support sorting by one dimension at a time only.
		if ($this->request->orderField) {
			$orderField = str_replace('-', '_', $this->request->orderField);
		} elseif ($model::hasField('modified')) {
			$orderField = 'modified';
		} elseif ($model::hasField('created')) {
			$orderField = 'created';
		} elseif ($model::hasField('published')) {
			$orderField = 'published';
		} else {
			$orderField = 'id';
		}
		if (strpos($orderField, '|') !== false) {
			$orderFields = explode('|', $orderField);
		} else {
			$orderFields = [$orderField];
		}

		// Figure out the order direction.
		if ($this->request->orderDirection) {
			$orderDirection = strtoupper($this->request->orderDirection);
		} else {
			$orderDirection = 'DESC';
		}

		foreach ($orderFields as $orderField) {
			// Enable relations if we're ordering by a relation's field.
			if (preg_match('/^(.*)\./', $orderField, $matches)) {
				$query['with'][] = $matches[1];
			}
			$query['order'][$orderField] = $orderDirection;
		}
		$query['with'] = array_unique($query['with']);
		return $query;
	}
}

?>