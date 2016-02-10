<?php

use base_core\extensions\cms\Panes;
use base_core\extensions\cms\Settings;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\util\Inflector;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$site = Settings::read('site');

// Remove when every page uses new rich page title.
if (!isset($page)) {
	$page = [];
}

// Rich page title.
$map = [
	'add' => $t('creating'),
	'edit' => $t('editing'),
	'index' => $t('listing'),
];
$page += [
	'action' => isset($map[$this->_request->action]) ? $map[$this->_request->action] : null,
	'empty' => $t('untitled')
];

if ($page['type'] == 'multiple') {
	$this->title(ucfirst($page['object']));
} elseif ($page['type'] == 'single') {
	if ($page['title']) {
		$this->title(ucfirst($page['action']) . " {$page['title']} - " . ucfirst($page['object']));
	} else {
		$this->title(ucfirst($page['action']) . " {$page['empty']} - " . ucfirst($page['object']));
	}
} elseif ($page['type'] == 'standalone') {
	$this->title("{$page['object']}");
}

// Ensure meta is set, some pages may not yet use it.
if (!isset($meta)) {
	$meta = [];
}

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
			App = <?php echo json_encode($app, JSON_PRETTY_PRINT) ?>
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
		$classes = ['layout-admin'];

		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<?=$this->view()->render(['element' => 'messages'], compact('flash'), [
			'library' => 'base_core'
		]) ?>

		<div id="modal" class="hide">
			<div class="controls"><div class="close">×</div></div>
			<div class="content"></div>
		</div>
		<div id="modal-overlay" class="hide"></div>

		<div id="container">
			<header class="header--main">
				<h1 class="h-super-alpha header--main__site">
					<?= $this->html->link($site['title'], [
						'controller' => 'pages', 'action' => 'home',
						'library' => 'base_core', 'admin' => true
					]) ?>
				</h1>
				<h2 class="h-super-alpha header--main__rpt rich-page-title">
					<?php if ($page['type'] != 'standalone'): ?>
						<span class="action"><?= $page['action'] ?></span>
					<?php endif ?>
					<span class="object"><?= $page['object'] ?></span>
					<?php if ($page['type'] == 'single'): ?>
						<span class="title" data-empty="<?= $page['empty'] ?>">
							<?= $page['title'] ?: $page['empty'] ?>
						</span>
					<?php endif ?>
					<?php foreach ($meta as $name => $value): ?>
						<?php if ($value === null) continue ?>
						<span class="meta"><?= $value ?></span>
					<?php endforeach ?>
				</h2>
				<div class="nav-top">
					<?php if ($authedUser): ?>
						<a
							href="<?= $this->url([
								'controller' => 'users', 'action' => 'edit',
								'id' => $authedUser->id,
								'library' => 'base_core', 'admin' => true
							])?>"
							class="button plain"
						>
							<?php echo $t('Moin {:name}!', [
								'name' => '<span class="nav-top__name">' . strtok($authedUser->name, ' ') . '</span>'
							]) ?>
							<span class="nav-top__role">
								(<?= $authedUser->role ?>)
							</span>
						</a>

						<?php if (isset($authedUser->original)): ?>
							<?= $this->html->link($t('Become <span class="nav-top__name">{:name}</span> again', ['name' => strtok($authedUser->original['name'], ' ')] ), [
								'library' => 'base_core',
								'controller' => 'users', 'action' => 'debecome',
								'admin' => true
							], ['class' => 'button plain', 'escape' => false]) ?>
						<?php endif ?>

						<?= $this->html->link($t('Logout'), [
							'controller' => 'users', 'action' => 'logout',
							'library' => 'base_core', 'admin' => true
						], ['class' => 'button logout plain']) ?>

						<?= $this->html->link($t('Site'), '/', [
							'scope' => 'app',
							'target' => 'new',
							'class' => 'view-site button plain inverse'
						]) ?>
					<?php endif ?>
				</div>
			</header>
			<?php
				$panes = Panes::read($this->_request);
				$pane = null;
				foreach ($panes as $item) {
					if ($item['active']) {
						$pane = $item;
						break;
					}
				}
			?>
			<div class="content-wrapper clearfix">
				<nav class="nav-panes-actions tabs-h">
				<?php foreach ($pane['panes'] as $item): ?>
						<?= $this->html->link($item['title'], $item['url'], [
							'class' => $item['active'] ? 'active tab-h' : 'tab-h'
						]) ?>
					<?php endforeach ?>
				</nav>
				<nav class="nav-panes-groups tabs-v">
					<?php foreach ($panes as $name => $item): ?>
						<?= $this->html->link($item['title'], $item['url'], [
							'class' => 'tab-v tab-' . strtolower(Inflector::slug($item['name'])) .  ($item['active'] ? ' active' : null),
							// FIXME Fixes "back to site" string URL, Should be using the Sites feature instead.
							'scope' => is_string($item['url']) ? 'app' : 'admin'
						]) ?>
					<?php endforeach ?>
				</nav>
				<div id="content">
					<?php echo $this->content(); ?>
				</div>
			</div>
		</div>
		<footer class="main">
			<div class="nav-bottom">
				<div>
				<?php
					$products = [
						'base' => 'Bento',
						'cms' => 'Bureau',
						'billing' => 'Banque',
						'ecommerce' => 'Boutique',
					];
					$version = BASE_CORE_VERSION;

					$loaded = '';
					foreach (array_reverse($products) as $prefix => $title) {
						if (Libraries::get("{$prefix}_core")) {
							$loaded .= !$loaded ? $title : '<span class="ghost">+' . $title . '</span>';
						}
					}
					echo 'AD ' . $loaded . ' ' . BASE_CORE_VERSION;
				?>
				</div>
				<div class="copyright">
					© 2013&ndash;<?= date('Y') ?> <?= $this->html->link('Atelier Disko', 'http://atelierdisko.de', ['target' => 'new']) ?>
				</div>
			</div>
		</footer>
	</body>
</html>