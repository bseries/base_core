<script>
	App = {};

	App.assets = {
		base: '<?= $this->assets->base() ?>'
	};
	App.media = {
		base: '<?= $this->media->base() ?>',
		endpoints: {
			<?php $url = ['controller' => 'files', 'library' => 'cms_media', 'admin' => true] ?>
			index: '<?= $this->url($url + ['action' => 'api_index']) ?>',
			view: '<?= $this->url($url + ['action' => 'api_view', 'id' => '__ID__']) ?>',
			transfer: '<?= $this->url($url + ['action' => 'api_transfer']) ?>'
		}
	};
</script>