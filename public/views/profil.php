<?php
$title = 'Profil';
if (!isset($jvc)) {
  $jvc = new Jvc();
}

$avatar = 'http://image.jeuxvideo.com/avatar/a/l/alexandre-1426094326-24d708f3e9c6ffd682bd97f0119eb29b.jpg';
//$avatar = 'http://image.jeuxvideo.com/avatar/d/a/dark-homard-1439125779-911606c1bb59cb6db566aee2dfc40f5b.jpg';
$messages = 14426;
$days = 6414;
$inscription_month = 'mars 1998';
?>
<body class="body--no-bottom"></body>
<div class="sheet sheet--nothing-over">
  <img class="profile-avatar" src="<?= $avatar ?>">

  <div class="profile-card">
    <div class="profile-card__messages"><strong class="profile-card__number"><?= n($messages) ?></strong> messages</div>
    <div class="profile-card__days"><strong class="profile-card__number"><?= n($days) ?></strong> jours (<?= $inscription_month ?>)</div>
    <div class="profile-card__ratio"><strong class="profile-card__number"><?= n($messages / ($days + 1), 2) ?></strong> messages par jour</div>
  </div>

  <div class="profile-card profile-card--text">
    <div class="profile-card__header">Description personnelle</div>
      <p>Tristesse dans ma bouche! Amertume gonflant<br />
Gonflant mon pauvre cœur! Mes amours parfumées<br />
Adieu vont s'en aller! Adieu couilles aimées!<br />
Ô sur ma voix coupée adieu chibre insolent!</p>
<p>Le condamné à mort, Genet.</p>
  </div>

  <div class="profile-card">
    <div class="profile-card__header">Signature</div>
      <p> <a href="http://www.noelshack.com/2015-39-1443047294-pierre-menes-decouvrez-le-montant-de-son-salaire-photos.jpg" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="//image.noelshack.com/minis/2015/39/1443047294-pierre-menes-decouvrez-le-montant-de-son-salaire-photos.png" alt="http://www.noelshack.com/2015-39-1443047294-pierre-menes-decouvrez-le-montant-de-son-salaire-photos.jpg"/></a> </p>
  </div>

  <div class="back-button-container">
    <a class="button" href="javascript:history.back()">Retour</a>
  </div>
</div>
