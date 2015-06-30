<?php
$title = 'Rechercher un forum';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit();
}

$q = isset($_GET['q']) ? $_GET['q'] : '';
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
  </div>
</header>

<div class="sheet rechercheforum">
  <form action="/recherche_forum" method="get">
    <input class="rechercheforum-q input" type="text" autocorrect="off" placeholder="Rechercher un forum" name="q" value="<?= h($q) ?>" autofocus>
    <input type="submit" class="validate" value="Go">
  </form>

<?php
if ($q) {
  $db = new Db();
  $results = $db->search_forum($q);
  $replace_patterns = explode(' ', $q);
  foreach ($replace_patterns as $k => $v) {
    $replace_patterns[$k] = '#' . str_replace('#', '\#', preg_quote($v)) . '#i';
  }
  
  if (count($results) > 100) {
?>
  <p>Plus de 100 résultats, veullez affiner votre recherche.
<?php
    $results = array_slice($results, 0, 100);
  }
  if ($results): ?>
  <ul>
<?php endif;
  foreach ($results as $result): ?>
  <li><a href="/<?= $result['forum_id'] ?>-<?= $result['slug'] ?>"><?= preg_replace($replace_patterns, '<strong>$0</strong>', h($result['human'])) ?></a></li>
<? endforeach;
  if ($results): ?>
  </ul>
<?php else: ?>
  <p>Aucun résultat. Entrez un ou des mots (ou partie de mot) qui se trouvent dans le nom du forum.</p>
<?php endif;
}
?>
</div>
