<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\util\Set;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

trait AdminIndexTrait {

	public function admin_index() {
		$model = $this->_model;
		$conditions = [];
		$with = [];
		$order = [];

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
		if ($this->request->orderDirection) {
			$orderDirection = strtoupper($this->request->orderDirection);
		} else {
			$orderDirection = 'DESC';
		}
		$q = $this->request->filter;

		if (in_array('user', $orderFields)) {
			// Support virtual users and users as a single user alias.
			$with[] = 'VirtualUser';
			$order['VirtualUser'] = $orderDirection;
		}

		// Build query contraints.
		foreach ($orderFields as $orderField) {
			if (preg_match('/^(.*)\./', $orderField, $matches)) {
				// Enable relations if we're ordering by a relation's field.
				$with[] = $matches[1];
			}
			$order[$orderField] = $orderDirection;
		}

		// Hack to ensure model is initialized and its behaviors, too.
		$model::meta();
		if ($model::hasBehavior('Searchable')) {
			$conditions = Set::merge($conditions, $model::searchConditions($q));
		}
		// Handle pagination.
		Paginator::setDefaultItemCountPerPage($perPage = 25);
		Paginator::setDefaultScrollingStyle('Sliding');

		$count = $model::find('count', compact('conditions', 'with'));

		$paginator = new Paginator(new ArrayAdapter(
			range(1, $count)
		));
		$paginator->setCurrentPageNumber($page = $this->request->page ?: 1);
		$paginator->setCacheEnabled(false);

		$data = $model::find('all', [
			'conditions' => $conditions,
			'page' => $page,
			'limit' => $perPage,
			'order' => $order,
			'with' => array_unique($with)
		]);
		return compact('data', 'paginator', 'order') + $this->_selects();
	}
}

?>