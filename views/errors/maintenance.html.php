<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$duration = $this->_response->headers('Retry-After') / 60;

?>
<article>
	<h1 class="alpha">
		<span class="code"><?= $this->_response->status['code'] ?></span>
		<?= $this->title($t('Maintenance')) ?>
	</h1>
	<ul class="reason">
		<li>
			<?= $t("Some changes are made to the infrastructure and certain pages may be unavailable for a few minutes.") ?>
			<?php echo $t(
				'Maintenance is expected to be completed within a maximum of <strong>{:duration} minutes</strong>.',
				compact('duration')
			) ?>
		</li>
	</ul>
	<ul class="try">
		<li><?= $t('Refresh this page after a few minutes.') ?></li>
	</ul>
</article>