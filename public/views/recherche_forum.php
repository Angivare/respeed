<?php
$title = 'Recherche d’un forum';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit();
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<div class="sheet rechercheforum">
  <?php include '_header.php' ?>

  <form action="/recherche_forum" method="get">
    <input class="rechercheforum-q input" type="text" autocorrect="off" placeholder="Rechercher un forum" name="q" value="<?= h($q) ?>" autofocus>
    <input type="submit" class="validate" value="Go">
  </form>

<?php
if ($q) {
  $db = new Db();
  $results = $db->search_forum($q);
  $count = count($results);
  $replace_patterns = explode(' ', $q);
  foreach ($replace_patterns as $k => $v) {
    $replace_patterns[$k] = '#' . str_replace('#', '\#', preg_quote($v)) . '#i';
  }
  
  if ($count > 100) {
?>
  <p>Plus de 100 résultats. Affinez votre recherche.
<?php
    $results = array_slice($results, 0, 100);
  }
  if ($results): ?>
  <ul>
<?php endif;
  foreach ($results as $result) {
    $name = str_replace(['__STRONG__', '__/STRONG__'], ['<strong>', '</strong>'], h(preg_replace($replace_patterns, '__STRONG__$0__/STRONG__', $result['human'])));
    ?>
  <li><a href="/<?= $result['forum_id'] ?>-<?= $result['slug'] ?>"><?= $name ?></a></li>
<?php }
  if ($results): ?>
  </ul>
<?php else: ?>
  <p>Aucun résultat. Entrez un ou des mots (ou partie de mot) qui se trouvent dans le nom du forum.</p>
<?php endif;
}
?>
</div>
