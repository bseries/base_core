<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

?>
<article>
	<h1 class="alpha">
		<span class="code"><?= $this->_response->status['code'] ?></span>
		<?= $this->title($t('Service Unavailable')) ?>
	</h1>
	<ul class="reason">
		<li><?= $t('The service is currently offline and will be back online soon.') ?></li>
	</ul>
	<ul class="try">
		<li><?= $t('Refresh this page after a few minutes.') ?></li>
	</ul>
</article>