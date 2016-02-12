<?php

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$sites = Sites::registry(true);

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
		<!-- Basics -->
		<?php echo $this->html->charset() ?>
		<link rel="icon" href="<?= $this->assets->url('/base-core/ico/admin.png') ?>">

		<!-- SEO -->
		<?php echo $this->seo->title() ?>

		<!-- Compatibility -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<!-- Styles -->
		<?php echo $this->assets->style([
			'/base-core/css/reset',
			'/base-core/css/admin'
		]) ?>
		<?php echo $this->styles() ?>

		<!-- Global Application Object Definition -->
		<script>
			App = <?php echo json_encode($app, JSON_PRETTY_PRINT) ?>;
		</script>

		<!-- Scripts -->
		<?php
			$scripts = array_merge(
				['/base-core/js/require'],
				$this->assets->availableScripts('base', ['admin' => true]),
				$this->assets->availableScripts('view', ['admin' => true]),
				$this->assets->availableScripts('layout', ['admin' => true])
			);
		?>
		<?php echo $this->assets->script($scripts) ?>
		<?php echo $this->scripts() ?>
		<!-- Dynamically added -->
	</head>
	<?php
		$classes = ['layout-admin-blank'];

		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<?=$this->view()->render(['element' => 'messages'], compact('flash'), [
			'library' => 'base_core'
		]) ?>

		<div id="container">
			<header class="header--main rich-page-title">
				<h1 class="h-super-alpha header--main__site">
					<?= $this->html->link($sites->first()['title'], ['controller' => 'pages', 'action' => 'home', 'library' => 'base_core']) ?>
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