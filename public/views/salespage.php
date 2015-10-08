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

<style><?= file_get_contents('style_notconnected.css') ?></style>

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

  <h2 class="article__subheadline"><span class="article__subheadline-number">4</span> Et plus</h2>

  <p>Liens directs NoelShack. Avatars en grand. Design qui s’étire pour les grands écrans… Tout est repensé.</p>

  <h2 class="article__subheadline article__subheadline--gnap_gnap">L’avis de Gnap_Gnap :</h2>

  <iframe class="gnap_gnap-youtube" width="420" height="315" src="https://www.youtube.com/embed/U0QA6OMEP1c?rel=0&amp;showinfo=0&amp;cc_load_policy=1" frameborder="0" allowfullscreen></iframe>
  
  <p class="article__nb-messages">Plus de <span class="number">200 000</span> messages postés.</p>

  <a class="article__cta" id="cta" href="/connexion">Démarrez l’expérience JVForum</a>
  
  <div class="legalese">JVForum n’est pas affilié à <a class="legalese__link" href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</div>
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
