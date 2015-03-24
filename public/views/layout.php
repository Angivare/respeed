<!doctype html>
<meta charset="utf-8">
<title><?= $title ?></title>
<link rel="stylesheet" href="/style-1.css">
<?= $body ?>
<script>
var $is_connected = <?= $jvc->is_connected() ? 'true' : 'false' ?>
  , $forum = <?= $forum ? $forum : 'false' ?>
  , $topic = <?= $topic ? ("'" . ($_GET['topic'][0] == '0' ? '0' : '') . $topic . "'") : 'false' ?>
  , $topicNew = <?= isset($topicNew) ? $topicNew : 'false' ?>
  , $slug = <?= $slug ? "'{$slug}'" : 'false' ?>
  , $title = <?= $title ? "'{$title}'" : 'false' ?>
</script>
<script src="/scripts/jquery.js"></script>
<?php if ($jvc->is_connected()): ?>
<?php endif ?>
<script src="/scripts/app.js"></script>
