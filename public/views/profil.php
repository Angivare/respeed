<?php
require 'parser.php';

$pseudo = h($_GET['profil']);

foreach (fetch_profile($pseudo) as $k => $v) {
  ${$k} = $v;
}

$title = $pseudo;
?>
<?php if (isset($couverture)): ?>
<body class="body--no-bottom body--sheet" style="background: black url(<?= $couverture ?>) top center;">
<?php else: ?>
<body class="body--no-bottom body--sheet">
<?php endif ?>

<?php if (strcasecmp($pseudo, $_COOKIE['pseudo']) != 0 && is_in_blacklist($pseudo)): ?>
  <div class="card profile-card">
    <div>
      <span class="blacklist-toggle button-link" data-pseudo="<?= $pseudo ?>">Ne plus ignorer <?= $pseudo ?></span>
    </div>
  </div>
<?php endif ?>

<?php if (isset($banned)): ?>
  <div class="profile-ban-mention">Banni</div>
<?php endif ?>

<?php if (isset($messages) || isset($days)): ?>
  <div class="card profile-card">
<?php if (isset($messages)): ?>
    <div class="profile-card__messages"><strong class="profile-card__number"><?= n($messages) ?></strong> <?= $messages >= 2 ? 'messages' : 'message' ?></div>
<?php endif ?>
<?php if (isset($days)): ?>
    <div class="profile-card__days"><strong class="profile-card__number"><?= n($days) ?></strong> <?= $days >= 2 ? 'jours' : 'jour' ?> (<?= $month . ' ' . $year ?>)</div>
<?php endif ?>
<?php if (isset($ratio)): ?>
    <div class="profile-card__ratio"><strong class="profile-card__number"><?= n($ratio, $ratio < 10 ? 2 : 1) ?></strong> <?= $ratio >= 2 ? 'messages' : 'message' ?> par jour</div>
<?php endif ?>
  </div>
<?php endif ?>

<?php if (isset($avatar)): ?>
  <img class="profile-avatar" src="<?= $avatar ?>">
<?php endif ?>

<?php if (isset($signature)): ?>
  <div class="card profile-card profile-card--text">
    <div class="card__header">Signature</div>
      <?= $signature ?>
  </div>
<?php endif ?>

<?php if (isset($description)): ?>
  <div class="card profile-card profile-card--text">
    <div class="card__header">Description personnelle</div>
      <?= $description ?>
  </div>
<?php endif ?>

<?php if (strcasecmp($pseudo, $_COOKIE['pseudo']) != 0 && !is_in_blacklist($pseudo)): ?>
  <div class="card profile-card">
    <div>
      <span class="blacklist-toggle button-link" data-pseudo="<?= $pseudo ?>">Ignorer <?= $pseudo ?></span>
    </div>
  </div>
<?php endif ?>

  <div class="back-button-container">
    <a class="button" href="javascript:history.back()">Retour</a>
  </div>
  <div style="height: 1px"><!-- Hack for Safari iOS, otherwise no margin at the bottom --></div>
