<?php
$title = 'Smileys';
?>
<body class="body--no-bottom"></body>
<div class="sheet sheet--nothing-over">
<?php foreach ($stickers as $id => $stickers_category): ?>
<?php $i = 0 ?>
<table class="smileys-card">
<?php foreach ($stickers_category as $id => $code): ?>
<?php if ($i % 4 == 0):?>
  <tr>
<?php endif ?>
    <td class="smiley-cell">
      <div class="sticker-cell__sticker"><img class="sticker sticker--demo" src="/images/stickers/small/<?= $code ?>.png"></div>
      <div class="sticker-cell__code">:<?= $code ?>:</div>
    </td>
<?php $i++ ?>
<?php endforeach ?>
</table>
<?php endforeach ?>

  <div class="card card--smiley">
    <div class="enlarged-sticker-mention">Les stickers postés seuls sur un paragraphe sont agrandis.</div>
  </div>

  <div class="back-button-container">
    <a class="button" href="javascript:history.back()">Retour</a>
  </div>
</div>
