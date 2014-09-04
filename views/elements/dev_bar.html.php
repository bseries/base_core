<?php

use lithium\core\Environment;
use base_core\extensions\cms\Settings;
use Naneau\SemVer\Parser as SemVer;

$version = SemVer::parse(str_replace('__PROJECT_VERSION_BUILD__', 'dev', PROJECT_VERSION));

$preRelease = ($pre = $version->getPreRelease()) ? $pre->getGreek() : null;
$friendly = null;

if ($preRelease == 'alpha') {
	$friendly = 'preview';
}

?>
<div class="dev-bar">
	<div class="inner">
		<div class="dev-status dev-status-<?= $friendly ?>">
			<div>
				<span class="dev-friendly"><?= $friendly ?></span>
			</div>
			<div>
				<span class="dev-label">Version:</span>
				<span class="dev-project-version"><?= PROJECT_VERSION ?></span>
			</div>
			<div>
				<span class="dev-label">Environment:</span>
				<span class="dev-env"><?= Environment::get() ?></span>
			</div>
		</div>
	</div>
</div>