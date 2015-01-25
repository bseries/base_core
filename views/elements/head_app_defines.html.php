<?php

use base_core\extensions\net\http\ClientRouter;

$app = [
	'assets' => [
		'base' => $this->assets->base()
	],
	'media' => [
		'base' => $this->media->base()
	],
	'routes' => $routes
];

?>
<!-- Application Definitions -->
<script>
App = <?php echo json_encode($app, JSON_PRETTY_PRINT) ?>
</script>