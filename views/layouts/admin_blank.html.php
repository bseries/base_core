<?php

use lithium\core\Environment;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\security\Auth;
use cms_core\extensions\cms\Settings;

$site = Settings::read('site');
$locale = Environment::get('locale');

$flash = FlashMessage::read();
FlashMessage::clear();

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
		<link rel="icon" href="<?= $this->assets->url('/cms-core/ico/admin.png') ?>">

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<?php echo $this->assets->style([
			'/cms-core/css/reset',
			'/cms-core/css/admin'
		]) ?>
		<link href='https://fonts.googleapis.com/css?family=Anonymous+Pro:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
		<?php echo $this->styles() ?>
		<?=$this->view()->render(['element' => 'head_app_defines'], ['admin' => true], ['library' => 'cms_core']) ?>
		<?php
			$scripts = array_merge(
				['/cms-core/js/jquery'],
				['/cms-core/js/require'],
				$this->assets->availableScripts('base', ['admin' => true]),
				$this->assets->availableScripts('view', ['admin' => true]),
				$this->assets->availableScripts('layout', ['admin' => true])
			);
		?>
		<?php echo $this->assets->script($scripts) ?>
		<?php echo $this->scripts() ?>
		<?php if (Settings::read('googleAnalytics.default')): ?>
			<?=$this->view()->render(['element' => 'ga'], [], [
				'library' => 'cms_core'
			]) ?>
		<?php endif ?>
	</head>
	<?php
		$classes = ['layout-admin', 'layout-admin-blank'];

		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<div
			id="messages"
			<?php if ($flash): ?>
				data-flash-message="<?= $flash['message'] ?>"
				data-flash-level="<?= isset($flash['attrs']['level']) ? $flash['attrs']['level'] : 'neutral' ?>"
			<?php endif ?>
		>
		</div>

		<div id="modal" class="hide">
			<div class="controls"></div>
			<div class="content"></div>
		</div>
		<div id="modal-overlay" class="hide"></div>

		<div id="container">
			<header class="main rich-page-title">
				<h1 class="t-super-alpha">
					<?= $this->html->link($site['title'], ['controller' => 'pages', 'action' => 'home', 'library' => 'cms_core']) ?>
				</h1>
				<h2 class="t-super-alpha object">
					<?= $page['object'] ?>
				</h2>
			</header>
			<div class="content-wrapper clearfix">
			<div id="content">
				<?php echo $this->content(); ?>
			</div>
			</div>
		</div>
		<footer class="main">
			<div class="nav-bottom">
				<?php // Do not disclose software version and type ?>
				<div class="copyright">
					© <?= date('Y') ?> <?= $this->html->link('Atelier Disko', 'http://atelierdisko.de', ['target' => 'new']) ?>
				</div>
			</div>
		</footer>
	</body>
</html>