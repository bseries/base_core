<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'standalone',
		'object' => $t('Login')
	],
	'extraBodyClasses' => ['layout-admin-session']
]);

?>
<article class="session">
	<?=$this->form->create(null, ['url' => 'Users::login']) ?>
		<?=$this->form->field('email', ['type' => 'email', 'label' => 'E–Mail']) ?>
		<?=$this->form->field('password', ['type' => 'password', 'label' => 'Passwort']) ?>
		<?=$this->form->button($t('Login'), ['type' => 'submit', 'class' => 'large button login']) ?>
	<?=$this->form->end() ?>
</article>