    <div class="pagination-forum">
<?php if ($page > 1): ?>
      <div class="precedent">
        <a href="/<?= $forum ?>-<?= $slug ?>" class="debut"><span>Début</span></a>
<?php if ($page > 2): ?>
        <a href="/<?= $forum ?>-<?= $slug ?>/<?= $page - 1 ?>" class="precedent-lien"><span>&nbsp;</span></a>
<?php endif ?>
      </div>
<?php endif ?>
<?php if ($has_next_page): ?>
      <div class="suivant">
        <a href="/<?= $forum ?>-<?= $slug ?>/<?= $page + 1 ?>">Suivant</a>
      </div>
<?php endif ?>
      <div class="page <?= $page > 1 ? '' : 'hidden' ?>"><a href="/<?= $forum ?>-<?= $slug ?>/<?= $page ?>">Page <?= $page ?></a></div>
    </div>
