<?php
$title = 'Connexion à la modération';

if ($jvc->logged_into_moderation) {
  $error = 'Déjà connecté.';
}

if (isset($_POST['password'], $_POST['referer'])) {
  $password = h($_POST['password']);
  $referer = h($_POST['referer']);

  $result = $jvc->log_into_moderation($password);
  if ($result) {
    set_toast_for_next_page('Connecté à la modération');
    header('Location: ' . $referer);
    exit;
  }
  else {
    $error = $jvc->err();
  }
}

if (!isset($referer)) {
  $referer = isset($_SERVER['HTTP_REFERER']) ? h($_SERVER['HTTP_REFERER']) : '/';
}
?>

<div class="sheet">
  <div class="content no-menu">
    <h1 class="page-title"><?= $title ?></h1>

    <form class="form" action="/moderation" method="post">

      <div class="form__block">
        <p class="form__text">Connectez-vous à la modération pour pouvoir supprimer des messages, verrouiller des topics, kicker et sanctionner via DDB.</p>
      </div>

<?php if (isset($error)): ?>
      <div class="form__block">
        <div class="form__error"><?= $error ?></div>
      </div>
<?php endif ?>

      <div class="form__block">
        <input class="form__topic" type="password" name="password" placeholder="Mot de passe de modération" autofocus tabindex="1">
      </div>

      <input type="hidden" name="referer" value="<?= $referer ?>">

      <div class="form__block">
        <input class="button button--raised button--large button--scale" type="submit" value="Modérer" tabindex="2">
      </div>
    </form>

    <div class="back-button-container">
      <a class="button" href="<?= isset($result) ? $referer : 'javascript:history.back()' ?>">Retour</a>
    </div>
    <div style="height: 1px"><!-- Hack for Safari iOS, otherwise no margin at the bottom --></div>
  </div>
</div>
