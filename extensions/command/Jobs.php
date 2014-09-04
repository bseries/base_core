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

namespace base_core\extensions\command;

use base_core\extensions\cms\Jobs as CmsJobs;
use lithium\g11n\Message;

class Jobs extends \lithium\console\Command {

	public function run() {
		extract(Message::aliases());

		$this->header('Registered Recurring Jobs');
		$data = CmsJobs::read();
		$names = [];

		foreach ($data['recurring'] as $frequency => $jobs) {
			foreach ($jobs as $job) {
				$names[] = $job['name'];
				$this->out("- {$job['name']}, frequency: {$frequency}, via: {$job['library']}");
			}
		}
		$this->out();
		$name = $this->in($t('Enter job to run:'), array(
			'choices' => $names
		));

		$this->out($t('Running job...'), false);
		CmsJobs::runName($name);
		$this->out($t('done.'));
	}

	public function runFrequency($frequency) {
		CmsJobs::runFrequency($frequency);
	}
}

?>