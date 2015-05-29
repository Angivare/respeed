<?php
$title = 'JVForum';
$jvc = new Jvc();

if ($jvc->is_connected()) {
  header('Location: /accueil');
  exit;
}
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/connexion" class="site-login-link">Se connecter</a>
  </div>
</header>

<div class="sheet">
  <div class="content no-menu">
    <div class="salespage">


      <h1>Pas satisfait de Respawn ?</h1>

      <p>JVForum vous propose des forums plus agréables à utiliser.


      <h2>Une version mobile complète</h2>

      <p>
        <a href="http://image.noelshack.com/fichiers/2015/16/1429342667-screenshot-2015-04-18-09-04-12-jvshack.png" class="screen-mobile"><img src="http://image.noelshack.com/fichiers/2015/16/1429342667-screenshot-2015-04-18-09-04-12-jvshack.png"></img></a>
        <a href="http://image.noelshack.com/fichiers/2015/16/1429342674-screenshot-2015-04-18-09-04-21-jvshack.png" class="screen-mobile"><img src="http://image.noelshack.com/fichiers/2015/16/1429342674-screenshot-2015-04-18-09-04-21-jvshack.png"></img></a>
        <a href="http://image.noelshack.com/fichiers/2015/16/1429342685-screenshot-2015-04-18-09-04-54-jvshack.png" class="screen-mobile"><img src="http://image.noelshack.com/fichiers/2015/16/1429342685-screenshot-2015-04-18-09-04-54-jvshack.png"></img></a>

      <p>Citer et ignorer sur mobile sont désormais possibles. JVForum veut offrir une expérience complète sur tous vos appareils.

      <p>(À venir : Édit, MP.)


      <h2>Une version PC adaptée aux grands écrans</h2>

      <p>
        <a href="http://image.noelshack.com/fichiers/2015/16/1429342319-capture-d-ecran-2015-04-18-a-09-26-14-jvshack.png" class="screen-desktop"><img src="http://image.noelshack.com/fichiers/2015/16/1429342319-capture-d-ecran-2015-04-18-a-09-26-14-jvshack.png"></img></a>
        <a href="http://image.noelshack.com/fichiers/2015/16/1429342472-capture-d-ecran-2015-04-18-a-09-33-52-jvshack.png" class="screen-desktop"><img src="http://image.noelshack.com/fichiers/2015/16/1429342472-capture-d-ecran-2015-04-18-a-09-33-52-jvshack.png"></img></a>
      
      <p>Le design de Respawn gaspille l’espace disponible sur un grand écran. JVForum vous permet de voir plus d’informations d’un coup, et de moins avoir à scroller.


      <h2>Rafraîchissement en temps réel</h2>

      <p>Vous rafraîchissez souvent. JVForum le fait maintenant pour vous : les nouveaux messages sont affichés automatiquement, en temps réel.

      <p>(À venir : Indicateur de nouveaux messages dans l’onglet.)


      <h2>Un design efficient, et très rapide</h2>

      <p>JVForum se concentre sur l’essentiel, et la vitesse. Les pages sont préchargées juste avant votre clic/appui pour s’afficher instantanément.
      
      <h3 class="cta"><a href="/connexion">Commencez à utiliser JVForum &gt;</a></h3>
    </div>
  </div>
</div>

<footer class="site-footer">JVForum n’est pas affilié avec <a href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</footer>
