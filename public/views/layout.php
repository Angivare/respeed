<!doctype html>
<html lang="fr">
<meta charset="utf-8">
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="/style-<?= REVISION_NUMBER_STYLE ?>.css" data-instant-track>
<meta name="google-analytics-id" content="<?= GOOGLE_ANALYTICS_ID ?>">
<link class="js-favicon" rel="icon" href="/images/favicon.png">
<link rel="apple-touch-icon" href="/images/appicon.png">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#1E88E5">
<?php if (isset($blacklist)): ?>
<style id="blacklist-style"><?= generate_blacklist_style($blacklist) ?></style>
<?php endif ?>

<?= $body ?>

<div class="toast"><div class="toast__label"> </div></div>

<?php if (isset($blacklist)): ?>
<script>
var $forum = <?= $forum ? $forum : 'false' ?>
  , $topic = <?= $topic ? ("'" . ($_GET['topic'][0] == '0' ? '0' : '') . $topic . "'") : 'false' ?>
  , $topicNew = <?= isset($topicNew) ? $topicNew : 'false' ?>
  , $slug = '<?= $slug ?>'
  , $title = <?= $title ? "'{$title}'" : 'false' ?>
  , $page = <?= $page ? $page : 'false' ?>
<?php foreach ($token as $k => $v): ?>
  , $<?= $k ?> = '<?= $v ?>'
<?php endforeach ?>
  , $blacklist = <?= json_encode($blacklist) ?>
  , $blacklistNeedsUpdate = <?= !$blacklist_is_fresh ? 'true' : 'false' ?>
  , $freshness = <?= $db->get_favorites_freshness($jvc->user_id) ?>
</script>
<?php endif ?>

<?php
$files = scandir(dirname(__FILE__) . '/../scripts');
foreach ($files as $file) {
  if (substr($file, -3) != '.js') {
    continue;
  }
  $file_id = substr($file, 0, -3);
?>
<script src="/scripts/<?= $file_id ?>.<?= filemtime(dirname(__FILE__) . '/../scripts/' . $file) ?>.js" data-no-instant data-instant-track></script>
<?php
}
?>
