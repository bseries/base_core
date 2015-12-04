<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'standalone',
		'object' => $t('Dashboard')
	]
]);

?>
<article class="home">
	<div class="widgets">
		<?php foreach ($widgets as $item): ?>
			<div
				class="widget loading"
				data-widget-name="<?= $item['name'] ?>"
				data-widget-type="<?= $item['type']?>"
			></div>
		<?php endforeach ?>
	</div>
</article>