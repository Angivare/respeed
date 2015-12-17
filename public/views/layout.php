<!doctype html>
<html lang="fr">
<meta charset="utf-8">
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<?php
$files = scandir(dirname(__FILE__) . '/../styles');
foreach ($files as $file) {
  if (substr($file, -4) != '.css') {
    continue;
  }
  $file_id = substr($file, 0, -4);
?>
<link rel="stylesheet" href="/styles/<?= $file_id ?>.<?= filemtime(dirname(__FILE__) . '/../styles/' . $file) ?>.css" data-instant-track>
<?php
}
?>
<meta name="google-analytics-id" content="<?= GOOGLE_ANALYTICS_ID ?>">
<link class="js-favicon" rel="icon" href="/images/favicon.png">
<link rel="apple-touch-icon" href="/images/appicon.png">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="hsl(0, 0%, 0%)">
<style id="blacklist-style"><?= generate_blacklist_style($blacklist) ?></style>

<?= $body ?>

<div class="toast"><div class="toast__label"> </div></div>

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
