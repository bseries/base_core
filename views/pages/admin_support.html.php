<?php

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'standalone',
		'object' => $t('Support')
	]
]);

?>
<article>
	<?=$this->view()->render(['element' => 'contact'], ['item' => Settings::read('contact.exec')], [
		'library' => 'base_core'
	]) ?>
</article>