<?php

$pages = $paginator->getPages();
$base = ['action' => 'index'];

if ($this->_request->orderField) {
	$base['orderField'] = $this->_request->orderField;
	$base['orderDirection'] = $this->_request->orderDirection;
	$base['filter'] = $this->_request->filter ?: '';
}

?>
<?php if ($pages->pageCount > 1): ?>
	<nav class="nav-paging">
		<?php if (isset($pages->previous)): ?>
			<?= $this->html->link('←', $base + ['page' => $pages->previous], [
				'rel' => 'prev', 'class' => 'button'
			]) ?>
		<?php endif ?>
		<?php if (!in_array($pages->first, $pages->pagesInRange)): ?>
			<?= $this->html->link($pages->first, $base + ['page' => $pages->first], [
				'rel' => 'prev', 'class' => 'button'
			]) ?>
			&nbsp;&mdash;
		<?php endif ?>
		<?php foreach ($pages->pagesInRange as $page): ?>
			<?= $this->html->link($page, $base + ['page' => $page], [
				'class' => 'button' . ($page === $pages->current ? ' active' : '')
			]) ?>
		<?php endforeach ?>
		<?php if (!in_array($pages->last, $pages->pagesInRange)): ?>
			&mdash;&nbsp;
			<?= $this->html->link($pages->last, $base + ['page' => $pages->last], [
				'rel' => 'prev', 'class' => 'button'
			]) ?>
		<?php endif ?>
		<?php if (isset($pages->next)): ?>
			<?= $this->html->link('→', $base + ['page' => $pages->next], [
				'rel' => 'next', 'class' => 'button'
			]) ?>
		<?php endif ?>
	</nav>
<?php endif ?>