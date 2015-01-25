<?php

$discoverUrl = [
	'controller' => 'App', 'action' => 'discover', 'api' => true
];

if (!empty($admin)) {
	$discoverUrl += [
		'library' => 'base_core', 'admin' => true
	];
}

$app = [
	'assets' => [
		'base' => $this->assets->base()
	],
	'media' => [
		'base' => $this->media->base()
	],
	'api' => [
		'discover' => $this->url($discoverUrl)
	]
];

?>
<!-- App Defines -->
<script>
	App = <?php echo json_encode($app, JSON_PRETTY_PRINT) ?>
</script>