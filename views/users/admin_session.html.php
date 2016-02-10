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
	'extraBodyClasses' => ['session']
]);

?>
<article>
	<?php $this->security->sign() ?>
	<?=$this->form->create(null, ['url' => 'Users::login']) ?>
		<?=$this->form->field('email', ['type' => 'email', 'label' => $t('E–Mail')]) ?>
		<?=$this->form->field('password', ['type' => 'password', 'label' => $t('Password')]) ?>
		<?=$this->form->button($t('Login'), [
			'type' => 'submit',
			'class' => 'large plain inverse login button',
			'exclude' => true
		]) ?>
	<?=$this->form->end() ?>
</article>