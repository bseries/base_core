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

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

trait AdminIndexTrait {

	public function admin_index() {
		$model = $this->_model;
		$conditions = [];
		$with = [];

		// Handle sorting. We support sorting by one
		// dimension at a time only.
		if ($this->request->orderField) {
			$orderField = str_replace('-', '_', $this->request->orderField);
		} else {
			$orderField = 'modified';
		}
		if ($this->request->orderDirection) {
			$orderDirection = strtoupper($this->request->orderDirection);
		} else {
			$orderDirection = 'DESC';
		}
		$order = [$orderField => $orderDirection];

		if (preg_match('/^(.*)\./', $orderField, $matches)) {
			// Enable relations if we're ordering by a relation's field.
			$with[] = $matches[1];
		}
		if ($orderField === 'users') {
			// Support virtual users and users as a single user alias.
			$with[] = 'VirtualUser';
			$order['VirtualUser'] = $orderDirection;
		}

		// Handle pagination.
		Paginator::setDefaultItemCountPerPage($perPage = 25);
		Paginator::setDefaultScrollingStyle('Sliding');

		$count = $model::find('count', compact('conditions', 'with'));

		$paginator = new Paginator(new ArrayAdapter(
			range(0, $count)
		));
		$paginator->setCurrentPageNumber($page = $this->request->page ?: 1);
		$paginator->setCacheEnabled(false);

		$data = $model::find('all', [
			'conditions' => $conditions,
			'page' => $page,
			'limit' => $perPage,
			'order' => $order,
			'with' => $with
		]);

		return compact('data', 'paginator', 'order') + $this->_selects();
	}
}

?>