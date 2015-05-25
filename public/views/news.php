<?php
$title = 'Nouveautés';
$jvc = new Jvc();
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <div class="content no-menu news">
    <h1>Liste des nouveautés et changements</h1>
    
    <h2>Mai 2015</h2>
    
    <h3>Lundi 25 mai</h3>
    <ul>
      <li>Certains liens inutiles (titre d’un topic, page en cours) ont été retirés, pour éviter les clics accidentels sur mobile
    </ul>
    
    <h3>Mardi 19</h3>
    <ul>
      <li>Correction du bug de la mention édit (causé par un changement du code chez JVC)
    </ul>
    
    <h3>Jeudi 07</h3>
    <ul>
      <li>Refonte esthétique des champs de texte
      <li>iOS : autocorrection enlevée quand on tape son pseudo pour se connecter
      <li>Mobile : suppression de la liste des sous-forums sur topic
      <li><strong>Amélioration de l’affichage des dates</strong>
        <br>Les messages d’aujourd’hui ou d’hier plus vieux d’une heure montrent maintenant l’heure de post
    </ul>
    
    <h3>Mercredi 06</h3>
    <ul>
      <li>Suppression du bouton flottant pour poster (prennait trop de place sur petits écrans, et n’était pas intuitif)
      <li>Correction d’un bug causés par les pseudos tout en chiffre
    </ul>
    
    <h2>Avril 2015</h2>
    
    <h3>Mercredi 22</h3>
    <ul>
      <li><strong>Correction du bug empêchant de citer sur iOS</strong>
      <li>Ajout de la liste des nouveautés et changements, que vous êtes en train de lire
      <li>Les champs pour le captcha ne montrent plus les anciens captchas sauvegardés par le navigateur
      <li>Meilleure gestion des erreurs lors de la connexion
      <li>Correction d’une faille XSS lors de l’édit d’un de ses messages (rien de bien grave, donc)
      <li><strong>Correction du bug empêchant de poster sur des vieux topics</strong>
    </ul>
    
    <h3>Lundi 20</h3>
    <ul>
      <li><strong>Sortie de la bêta privée</strong>
    </ul>
  </div>
</div>
