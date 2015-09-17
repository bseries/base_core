<?php

use lithium\core\Environment;
use lithium\security\Auth;
use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$site = Settings::read('site');
$locale = Environment::get('locale');

// Remove when every page uses new rich page title.
if (!isset($page)) {
	$page = [];
}
$page += [
	'type' => null,
	'object' => null
];

?>
<!doctype html>
<html lang="<?= strtolower(str_replace('_', '-', $locale)) ?>">
	<head>
		<?php echo $this->html->charset() ?>
		<title><?php echo ($title = $this->title()) ? "{$title} - " : null ?>Admin – <?= $site['title'] ?></title>
		<link rel="icon" href="<?= $this->assets->url('/base-core/ico/admin.png') ?>">

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<?php echo $this->assets->style([
			'/base-core/css/reset',
			'/base-core/css/admin'
		]) ?>
		<link href='https://fonts.googleapis.com/css?family=Anonymous+Pro:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
		<?php echo $this->styles() ?>
		<?=$this->view()->render(
			['element' => 'define_app'],
			['admin' => true, 'routes' => $routes],
			['library' => 'base_core']
		) ?>
		<?php
			$scripts = array_merge(
				['/base-core/js/jquery'],
				['/base-core/js/require'],
				$this->assets->availableScripts('base', ['admin' => true]),
				$this->assets->availableScripts('view', ['admin' => true]),
				$this->assets->availableScripts('layout', ['admin' => true])
			);
		?>
		<?php echo $this->assets->script($scripts) ?>
		<?php echo $this->scripts() ?>
	</head>
	<?php
		$classes = ['layout-admin', 'layout-admin-blank'];

		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<?=$this->view()->render(['element' => 'messages'], compact('flash'), [
			'library' => 'base_core'
		]) ?>

		<div id="modal" class="hide">
			<div class="controls"></div>
			<div class="content"></div>
		</div>
		<div id="modal-overlay" class="hide"></div>

		<div id="container">
			<header class="header--main rich-page-title">
				<h1 class="h-super-alpha header--main__site">
					<?= $this->html->link($site['title'], ['controller' => 'pages', 'action' => 'home', 'library' => 'base_core']) ?>
				</h1>
				<h2 class="h-super-alpha object header--main__rpt">
					<?= $page['object'] ?>
				</h2>
			</header>
			<div class="content-wrapper clearfix">
			<div id="content">
				<?php echo $this->content(); ?>
			</div>
			</div>
		</div>
		<?php // Do not disclose software version and type and who did this. ?>
	</body>
</html>