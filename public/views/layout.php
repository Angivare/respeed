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
<meta name="theme-color" content="#039BE5">
<?php if (isset($blacklist)): ?>
<style id="blacklist-style"><?= generate_blacklist_style($blacklist) ?></style>
<?php endif ?>

<?= $body ?>

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
</script>
<?php endif ?>

<script src="/scripts/jquery-<?= REVISION_NUMBER_JS_JQUERY ?>.js" data-no-instant data-instant-track></script>
<script src="/scripts/fastclick-<?= REVISION_NUMBER_JS_FASTCLICK ?>.js" data-no-instant data-instant-track></script>
<script src="/scripts/instantclick-<?= REVISION_NUMBER_JS_INSTANTCLICK ?>.js" data-no-instant data-instant-track></script>
<script src="/scripts/loading-indicator-<?= REVISION_NUMBER_JS_LOADING_INDICATOR ?>.js" data-no-instant data-instant-track></script>
<script src="/scripts/jvcode-<?= REVISION_NUMBER_JS_JVCODE ?>.js" data-no-instant data-instant-track></script>
<script src="/scripts/app-<?= REVISION_NUMBER_JS_APP ?>.js" data-no-instant data-instant-track></script>
