<!doctype html>
<meta charset="utf-8">
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/style-<?= REVISION_NUMBER_STYLE ?>.css">
<meta name="google-analytics-id" content="<?= GOOGLE_ANALYTICS_ID ?>">
<?= $body ?>
<script>
var $is_connected = <?= $jvc->is_connected() ? 'true' : 'false' ?>
  , $forum = <?= $forum ? $forum : 'false' ?>
  , $topic = <?= $topic ? ("'" . ($_GET['topic'][0] == '0' ? '0' : '') . $topic . "'") : 'false' ?>
  , $topicNew = <?= isset($topicNew) ? $topicNew : 'false' ?>
  , $slug = <?= $slug ? "'{$slug}'" : 'false' ?>
  , $title = <?= $title ? "'{$title}'" : 'false' ?>
  , $page = <?= $page ? $page : 'false' ?>
<?php foreach($token as $k => $v): ?>
  , $<?= $k ?> = '<?= $v ?>'
<?php endforeach ?>
</script>
<script src="/scripts/jquery.min.js" data-no-instant></script>
<script src="/scripts/fastclick.js" data-no-instant></script>
<script src="/scripts/instantclick.js" data-no-instant></script>
<script src="/scripts/jvcode.js" data-no-instant></script>
<script src="/scripts/app-<?= REVISION_NUMBER_JS_APP ?>.js" data-no-instant></script>
