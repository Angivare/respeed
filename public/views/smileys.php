<?php
$title = 'Smileys';
if (!isset($jvc)) {
  $jvc = new Jvc();
}
?>
<?php foreach ($stickers as $stickers_category): ?>
<?php $i = 0 ?>
<table class="smileys-card">
<?php foreach ($stickers_category as $id => $code): ?>
<?php if ($i % 4 == 0):?>
  <tr>
<?php endif ?>
    <td class="smiley-cell">
      <div class="sticker-cell__sticker"><img class="sticker sticker--demo" src="http://jv.stkr.fr/p3w/<?= $id ?>"></div>
      <div class="sticker-cell__code"><span>:<?= $code ?>:</span></div>
    </td>
<?php $i++ ?>
<?php endforeach ?>
</table>
<?php endforeach ?>
