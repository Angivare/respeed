<!doctype html>
<meta charset="utf-8">
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/style-<?= REVISION_NUMBER_STYLE ?>.css">
<meta name="google-analytics-id" content="<?= GOOGLE_ANALYTICS_ID ?>">
<link rel="icon" href="/images/favicon.png">
<link rel="apple-touch-icon" href="/images/appicon.png">
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
<script src="/scripts/jquery-<?= REVISION_NUMBER_JS_JQUERY ?>.js" data-no-instant></script>
<script src="/scripts/fastclick-<?= REVISION_NUMBER_JS_FASTCLICK ?>.js" data-no-instant></script>
<script src="/scripts/instantclick-<?= REVISION_NUMBER_JS_INSTANTCLICK ?>.js" data-no-instant></script>
<script src="/scripts/jvcode-<?= REVISION_NUMBER_JS_JVCODE ?>.js" data-no-instant></script>
<script src="/scripts/app-<?= REVISION_NUMBER_JS_APP ?>.js" data-no-instant></script>
