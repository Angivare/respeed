<!doctype html>
<html lang="fr">
<meta charset="utf-8">
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<?php if (file_exists(dirname(__FILE__) . '/../styles/special/combined.css')): ?>
<link rel="stylesheet" href="/styles/special/combined.<?= filemtime(dirname(__FILE__) . '/../styles/special/combined.css') ?>.css" data-instant-track>
<?php
else:
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
endif;
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
  , $topicMode = <?= isset($topic_mode) ? $topic_mode : 'false' ?>
  , $topicIdOldOrNew = <?= isset($topic_id_old_or_new) ? $topic_id_old_or_new : 'false' ?>
  , $topicIdNew = <?= isset($topic_id_new) ? $topic_id_new : 'false' ?>
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

<?php foreach ($javascripts_after_files as $script): ?>
<script><?= $script ?></script>
<?php endforeach ?>
