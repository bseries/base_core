<?php

$pages = $paginator->getPages();

?>
<?php if ($pages->pageCount > 0): ?>
	<nav class="nav-paging">
		<?php if (isset($pages->previous)): ?>
			<?= $this->html->link('prev', ['action' => 'index', 'page' => $pages->previous], [
				'rel' => 'prev', 'class' => 'button'
			]) ?>
		<?php endif ?>
		<?php foreach ($pages->pagesInRange as $page): ?>
			<?= $this->html->link($page, ['action' => 'index', 'page' => $page], [
				'class' => 'button' . ($page === $pages->current ? ' active' : '')
			]) ?>
		<?php endforeach ?>
		<?php if (isset($pages->next)): ?>
			<?= $this->html->link('next', ['action' => 'index', 'page' => $pages->next], [
				'rel' => 'next', 'class' => 'button'
			]) ?>
		<?php endif ?>
	</nav>
<?php endif ?>