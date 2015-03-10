<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($id, $options + ['scope' => 'base_core', 'default' => $message]);
};

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha">
		<span class="code"><?= $this->_response->status['code'] ?></span>
		<?= $this->title($t('Internal Server Error')) ?>
	</h1>
	<ul class="reason">
		<li><?= $t("An unexpected technical problem occurred.") ?></li>
	</ul>
	<ul class="try">
		<li><?php echo $t(
			'Go to the frontpage at <strong>{:url}</strong>.',
			[
				'url' => $this->html->link(
					$this->url('Pages::home', ['absolute' => true]), 'Pages::home'
				)
			]
		)?></li>
	</ul>
</article>