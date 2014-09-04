<script>
App = {
	assets: {
		base: "<?= $this->assets->base() ?>"
	},
	media: {
		base: "<?= $this->media->base() ?>"
	},
	api: {
		<?php if ($admin): ?>
			discover: "<?= $this->url(['library' => 'base_core', 'action' => 'api_discover', 'admin' => true, 'controller' => 'App']) ?>"
		<?php else: ?>
			discover: "<?= $this->url(['action' => 'api_discover', 'controller' => 'App']) ?>"
		<?php endif ?>
	}
}
</script>