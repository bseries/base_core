<?php

$pages = $paginator->getPages();

?>
<?php if ($pages->pageCount > 0): ?>
	<nav class="nav-paging">
		<?php if (isset($pages->previous)): ?>
			<?= $this->html->link('prev', ['action' => 'index', 'page' => $pages->previous], [
				'rel' => 'prev', 'class' => 'button large'
			]) ?>
		<?php endif ?>
		<?php if (!in_array($pages->first, $pages->pagesInRange)): ?>
			<?= $this->html->link($pages->first, ['action' => 'index', 'page' => $pages->first], [
				'rel' => 'prev', 'class' => 'button large'
			]) ?>
			…
		<?php endif ?>
		<?php foreach ($pages->pagesInRange as $page): ?>
			<?= $this->html->link($page, ['action' => 'index', 'page' => $page], [
				'class' => 'button large' . ($page === $pages->current ? ' active' : '')
			]) ?>
		<?php endforeach ?>
		<?php if (!in_array($pages->last, $pages->pagesInRange)): ?>
			…
			<?= $this->html->link($pages->last, ['action' => 'index', 'page' => $pages->last], [
				'rel' => 'prev', 'class' => 'button large'
			]) ?>
		<?php endif ?>
		<?php if (isset($pages->next)): ?>
			<?= $this->html->link('next', ['action' => 'index', 'page' => $pages->next], [
				'rel' => 'next', 'class' => 'button large'
			]) ?>
		<?php endif ?>
	</nav>
<?php endif ?>