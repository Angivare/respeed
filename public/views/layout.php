<!doctype html>
<meta charset="utf-8">
<title><?= $title ?></title>
<link rel="stylesheet" href="/style-1.css">
<?= $body ?>
<script>
var is_connected = <?= $jvc->is_connected() ? 'true' : 'false' ?>
</script>
<script src="/scripts/jquery.js"></script>
<script src="/scripts/app.js"></script>
