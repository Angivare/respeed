<?php
$jvc = new Jvc();
if ($jvc->is_connected()) {
  header('Location: /accueil');
  exit;
}
?>
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

.article {
  margin: auto;
  padding: 0 15px;
  max-width: 600px;
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


.article__hero-container {
  margin: 2em 0;
}

@media (MAX-width: 1024px) {
  .article__hero-container {
    text-align: center;
  }
}

.article__hero {
  display: inline-block;
  box-shadow: 0 0 50px rgba(0,0,0,.2);
}

.article__hero--mobile {
  background: red;
}

.article__hero--mobile img {
  max-height: 250px;
}

@media (min-width: 1024px) {
  .article__hero--mobile {
    display: none;
  }
}

@media (MAX-width: 1024px) {
  .article__hero--desktop {
    display: none;
  }
}

.article__hero img {
  display: block;
}

.article__hero--desktop img {
  width: 500px;
  height: 304px;
}

.desktop-twist {
  display: none;
}

@media (min-width: 1025px) {
  .mobile-twist {
    display: none;
  }

  .desktop-twist {
    display: block;
  }
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

.article__image {
  margin: 1em -15px;
  border: solid rgba(0,0,0,.2);
  border-width: 1px 0;
  box-shadow: 0 0 5px rgba(0,0,0,.2);
}

@media (min-width: 362px) {
  .article__image {
    border-width: 1px;
    margin: 1em auto;
  }
}

@media (min-width: 500px) {
  .article__image {
    margin: 1em 0;
  }
}

.article__image--options-mobile {
  /* The delimitation between .article__image & .article__image--options-mobile is a mess */

  background: url(/images/salespage/options_mobile.png) right;
  background-size: 360px;
  height: 71px;
  max-width: 360px;
}

.article__subheadline--gnap_gnap {
  text-align: center;
}

.article__nb-messages {
  text-align: center;
  font-size: .95em;
  font-weight: bold;
  margin: 2em 0;
}

.number {
  word-spacing: -1px;
}

.gnap_gnap-youtube {
  width: 100%;
  width: -webkit-calc(100% + 30px);
  width:         calc(100% + 30px);
  height: 270px;
  margin: 0 -15px;
}

@media (min-width: 600px) {
  .gnap_gnap-youtube {
    height: 340px;
  }
}

.article__cta {
  display: block;
  background: #288FFF;
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

.article__cta:hover {
  box-shadow: 0 3px 10px rgba(0,0,0,.3);
  background: #007aff;
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

<article class="article">
  <h1 class="article__headline">Pas satisfait de Respawn ?</h1>

  <p class="article__tagline">JVForum rend les forums plus agréables à utiliser.</p>

  <div class="article__hero-container"><a class="article__hero article__hero--mobile" href="/images/salespage/screen_mobile_full.png" target="_blank"><img src="/images/salespage/screen_mobile_full.png"></a></div>

  <div class="article__hero-container"><a class="article__hero article__hero--desktop" href="/images/salespage/screen_desktop_full.png" target="_blank"><img src="/images/salespage/screen_desktop_full.png"></a></div>

  <div class="mobile-twist">
    <h2 class="article__subheadline"><span class="article__subheadline-number">1</span> Version mobile complète</h2>
    
    <figure class="article__image article__image--options-mobile"></figure>

    <p>Modifiez. Supprimez. Citez. <!--Votez. Conversez en privé.--> <!--Tout y est.--> Et plus à venir.</p>

    <h2 class="article__subheadline"><span class="article__subheadline-number">2</span> Messages en temps réel</h2>

    <p>Plus besoin de rafraîchir.</p>
    
    <p>Sur PC, l’onglet vous notifie des nouvelles réponses.</p>
  </div>

  <div class="desktop-twist">
    <h2 class="article__subheadline"><span class="article__subheadline-number">1</span> Messages en temps réel</h2>

    <p>Plus besoin de rafraîchir.</p>
    
    <p>Sur PC, l’onglet vous notifie des nouvelles réponses.</p>

    <h2 class="article__subheadline"><span class="article__subheadline-number">2</span> Version mobile complète</h2>
    
    <figure class="article__image article__image--options-mobile"></figure>

    <p>Modifiez. Supprimez. Citez. <!--Votez. Conversez en privé.--> <!--Tout y est.--> Et plus à venir.</p>
  </div>

  <h2 class="article__subheadline"><span class="article__subheadline-number">3</span> Hyper fluide</h2>

  <p>Les pages s’affichent instantanément grâce au préchargement.</p>

  <h2 class="article__subheadline"><span class="article__subheadline-number">4</span> Et plus. Bien plus</h2>

  <p>Liens directs NoelShack. Avatars en grand. Design qui s’étire pour les grands écrans… Tout est repensé.</p>

  <h2 class="article__subheadline article__subheadline--gnap_gnap">L’avis de Gnap_Gnap :</h2>

  <iframe class="gnap_gnap-youtube" width="420" height="315" src="https://www.youtube.com/embed/U0QA6OMEP1c?rel=0&amp;showinfo=0&amp;cc_load_policy=1" frameborder="0" allowfullscreen></iframe>
  
  <p class="article__nb-messages">Plus de <span class="number">200 000</span> messages postés.</p>

  <a class="article__cta" id="cta" href="/connexion">Démarrez l’expérience JVForum</a>
  
  <div class="article__legalese">JVForum n’est pas affilié à <a class="article__legalese-link" href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</div>
</article>

<?php if (GOOGLE_ANALYTICS_ID): ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', '<?= GOOGLE_ANALYTICS_ID ?>', 'auto');
ga('set', 'dimension1', 'Guest')
ga('send', 'pageview')
</script>
<?php endif ?>
<script src="/scripts/fastclick-<?= REVISION_NUMBER_JS_FASTCLICK ?>.js" onload="FastClick.attach(document.body)"></script>
