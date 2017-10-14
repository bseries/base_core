<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
		$this->out(JobsCore::runFrequency($frequency) ? 'OK' : 'FAILED');
	}
}

?>