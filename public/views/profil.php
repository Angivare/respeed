<?php
require 'parser.php';
if (!isset($jvc)) {
  $jvc = new Jvc();
}

$pseudo = h($_GET['profil']);


foreach (fetch_profile($pseudo) as $k => $v) {
  $$k = $v;
}

$title = $pseudo;
?>
<body class="body--no-bottom"></body>
<div class="sheet sheet--nothing-over">
<?php if (isset($avatar)): ?>
  <img class="js-profile-avatar profile-avatar" data-src="<?= $avatar ?>">
<?php endif ?>

<?php if (isset($banned)): ?>
  <div class="profile-ban-mention">Pseudo banni.</div>
<?php endif ?>

<?php if (isset($messages) || isset($days)): ?>
  <div class="profile-card">
<?php if (isset($messages)): ?>
    <div class="profile-card__messages"><strong class="profile-card__number"><?= n($messages) ?></strong> messages</div>
<?php endif ?>
<?php if (isset($days)): ?>
    <div class="profile-card__days"><strong class="profile-card__number"><?= n($days) ?></strong> jours (<?= $month . ' ' . $year ?>)</div>
<?php endif ?>
<?php if (isset($ratio)): ?>
    <div class="profile-card__ratio"><strong class="profile-card__number"><?= n($ratio, $ratio < 10 ? 2 : 1) ?></strong> messages par jour</div>
<?php endif ?>
  </div>
<?php endif ?>

<?php if (isset($signature)): ?>
  <div class="profile-card">
    <div class="profile-card__header">Signature</div>
      <?= $signature ?>
  </div>
<?php endif ?>

<?php if (isset($description)): ?>
  <div class="profile-card profile-card--text">
    <div class="profile-card__header">Description personnelle</div>
      <?= $description ?>
  </div>
<?php endif ?>

  <div class="back-button-container">
    <a class="button" href="javascript:history.back()">Retour</a>
  </div>
</div>
