<?php
/**
 * Base
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use Zend\Paginator\Adapter\ArrayAdapter;
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
			$query = $model::searchQuery($this->request->filter, $query);
		}

		$query = $this->_order($query);

		$data = $model::find('all', $query);
		$paginator = $this->_paginator($query);

		$useOwner = Settings::read('security.checkOwner') && Gate::checkRight('owner');

		return compact('data', 'paginator', 'useOwner') + $this->_selects();
	}

	// Handle pagination.
	protected function _paginator($query) {
		$model = $this->_model;

		$itemsPerPage = $query['limit'];
		$page = $query['page'];

		Paginator::setDefaultItemCountPerPage($itemsPerPage);
		Paginator::setDefaultScrollingStyle('Sliding');

		unset($query['page']);
		unset($query['limit']);
		unset($query['order']); // Optimize.
		$count = $model::find('count', $query);

		$paginator = new Paginator(new ArrayAdapter(
			range(1, $count)
		));
		$paginator->setCurrentPageNumber($page);
		$paginator->setCacheEnabled(false);

		return $paginator;
	}

	protected function _order($query) {
		// Normalize order field and direction.
		// We support sorting by one dimension at a time only.
		if ($this->request->orderField) {
			$orderField = str_replace('-', '_', $this->request->orderField);
		} else {
			$orderField = 'modified';
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
			// Support virtual users and users as a single user alias.
			if (preg_match('/^User\.(.*)/i', $orderField, $matches)) {
				$query['order']['VirtualUser.' . $matches[1]] = $orderDirection;
				$query['with'][] = 'VirtualUser';
			}

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