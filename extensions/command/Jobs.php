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

namespace base_core\extensions\command;

use base_core\async\Jobs as JobsCore;

class Jobs extends \lithium\console\Command {

	public function run($name = null) {
		if (!$name) {
			$this->header('Registered Recurring Jobs');
			$data = JobsCore::read();
			$names = [];

			foreach ($data['recurring'] as $frequency => $jobs) {
				foreach ($jobs as $job) {
					$names[] = $job['name'];
					$this->out("- {:green}{$job['name']}{:end}, frequency: {$frequency}");
				}
			}
			$this->out();
			$name = $this->in('Enter job to run:');
		}

		$this->out("Running job `{:green}{$name}{:end}`... ", false);
		$this->out(JobsCore::runName($name) ? 'OK' : 'FAILED');
	}

	public function runFrequency($frequency) {
		$this->out("Running all jobs for frequency `{:green}{$frequency}{:end}`... ", false);
		$this->out(CmsCore::runFrequency($frequency) ? 'OK' : 'FAILED');
	}
}

?>