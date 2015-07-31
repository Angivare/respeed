<?php include 'config.php' ?>
<!doctype html>
<meta charset="utf-8">
<title>JVForum</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Les forums de jeuxvideo.com, en bien.">
<link class="js-favicon" rel="icon" href="/images/favicon.png">
<meta name="theme-color" content="hsl(240, 11%, 40%)">

<style>
body {
  font: 16px/1.45 -apple-system, sans-serif;
  margin: 0;
  background: #eeeef0;
}

@media (min-width: 360px) {
  body {
    font-size: 18px;
  }
}

.header {
  display: table;
  width: 100%;
  border-bottom: 1px solid black;
  background: #445;
  color: white;
  padding: 7px 0;
  box-shadow: inset 0 -2px 2px rgba(0,0,0,.15);
}

.header__title {
  display: table-cell;
  font-size: 1.5em;
  font-weight: bold;
  padding-left: 15px;
}

.header__connect-button {
  display: table-cell;
  text-align: right;
  padding-right: 15px;
  color: rgba(255,255,255,.75);
  font-size: 14px;
  vertical-align: middle;
}

.article {
  margin: auto;
  padding: 0 15px;
  max-width: 700px;
  color: #111;
}

.article__headline {
  font-size: 1.45em;
  margin-bottom: .7em;
}

@media (min-width: 400px) {
  .article__headline {
    font-size: 1.65em;
  }
}

.article__tagline {
  margin-bottom: 1.5em;
}

.article__subheadline {
  font-size: 1.2em;
}

.article__subheadline-number {
  border-radius: 9em;
  background: firebrick;
  color: white;
  display: inline-block;
  text-align: center;
  width: 30px;
  height: 30px;
  line-height: 30px;
  margin-right: 4px;
}

.article__subheadline--social-proof {
  text-align: center;
}

.article__nb-messages {
  text-align: center;
  font-size: .9em;
  font-weight: bold;
  margin: 2em 0;
}

.article__nb-messages--skeptical {
  font-size: 1em;
}

.number {
  word-spacing: -1px;
}

.gnap-youtube {
  width: 100%;
  width: -webkit-calc(100% + 30px);
  width:         calc(100% + 30px);
  height: 270px;
  margin: 0 -15px;
}

.article__cta {
  background: #007aff;
  color: white;
  text-decoration: none;
  font-weight: bold;
  text-align: center;
  border-radius: 3px;
  box-shadow: 0 3px 5px rgba(0,0,0,.2);
  padding: 1em 0;
  font-size: 1em;
  margin: 2em 0;
}

@media (max-width: 320px) {
  .article__cta {
    margin-right: -7px;
    margin-left: -7px;
    font-size: 1.1em;
  }
}

.article__legalese {
  font-size: .75em;
  text-align: center;
  margin: 50px 0 20px;
}

.article__legalese-link {
  color: inherit;
}
</style>

<header class="header">
  <div class="header__title">JVForum</div>
  <div class="header__connect-button">Connexion</div>
</header>

<article class="article">
  <h1 class="article__headline">Pas satisfait de Respawn ?</h1>

  <p class="article__tagline">JVForum rend les forums meilleurs.</p>

  <h2 class="article__subheadline"><span class="article__subheadline-number">1</span> Version mobile complète</h2>

  <p>Modifiez. Supprimez. Citez. <!--Votez. Conversez en privé.--> Tout y est.</p>

  <h2 class="article__subheadline"><span class="article__subheadline-number">2</span> Messages en temps réel</h2>

  <p>Plus besoin de rafraîchir.</p>
  
  <p>Sur PC, l’onglet vous notifie des nouvelles réponses.</p>

  <h2 class="article__subheadline"><span class="article__subheadline-number">3</span> Hyper fluide</h2>

  <p>Les pages s’affichent instantanément grâce au préchargement.</p>

  <h2 class="article__subheadline"><span class="article__subheadline-number">4</span> Et plus. Bien plus</h2>

  <p>Liens directs NoelShack. Avatars en grand… Tout est repensé.</p>
  
  <p class="article__nb-messages">Plus de <span class="number">200 000</span> messages postés.</p>

  <!--<h2 class="article__subheadline article__subheadline--social-proof">L’avis de Gnap_Gnap :</h2>

  <iframe class="gnap-youtube" width="420" height="315" src="https://www.youtube.com/embed/U0QA6OMEP1c" frameborder="0" allowfullscreen></iframe>-->

  <div class="article__cta" href="/connexion">Démarrez l’expérience JVForum</div>
  
  <div class="article__legalese">JVForum n’est pas affilié à <a class="article__legalese-link" href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</div>
</article>

<script>
var googleAnalyticsID = '<?= GOOGLE_ANALYTICS_ID ?>'

// Preload style and scripts
</script>
