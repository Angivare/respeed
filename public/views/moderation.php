<?php
$title = 'Connexion à la modération';

$password = isset($_POST['password']) ? $_POST['password'] : null;
if ($password) {
  $result = $jvc->log_into_moderation($password);
  if ($result) {

  }
  else {
    echo $jvc->err();
  }
}
?>

<?php if ($jvc->logged_into_moderation): ?>
Déjà connecté.
<?php else: ?>
<form action="/moderation" method="post">
  <input type="password" name="password">
  <input type="submit">
</form>
<?php endif ?>
