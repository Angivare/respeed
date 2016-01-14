<?php

$pseudo = isset($_GET['pseudo']) ? h($_GET['pseudo']) : '';
$pseudoLowercase = strtolower($pseudo);

$message_id = isset($_GET['message_id']) ? (int)$_GET['message_id'] : 0;

$title = 'Kicker ' . $pseudo;

if (!$jvc->logged_into_moderation) {
  header('Location: /moderation');
  exit;
}

if (isset($_POST['category'], $_POST['rationale'], $_POST['referer'], $_POST['message_id'])) {
  $message_id = (int)$_POST['message_id'];
  $category = (int)$_POST['category'];
  $rationale = $_POST['rationale'];
  $referer = h($_POST['referer']);
  $kick_result = $jvc->kick($message_id, $category, $rationale);
  if ($kick_result) {
    set_toast_for_next_page($pseudo . ' kické');
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
    <h1 class="page-title">Kicker <?= $pseudo ?></h1>

    <form class="form" action="/kick/<?= $pseudo ?>" method="post">
<?php if (isset($error)): ?>
      <div class="form__block">
        <div class="form__error"><?= $error ?></div>
      </div>
<?php endif ?>

      <div class="form__block">
        <select class="form__select" name="category" tabindex="1">
          <option value="">Choix du motif</option>
          <optgroup label="Contenus illicites">
            <option value="1">Pédopornographie</option>
            <option value="2">Incitation à la haine, discrimination</option>
            <option value="3">Mise en danger des personnes</option>
            <option value="4">Diffamation, menaces</option>
            <option value="5">Piratage, non respect des droits d'auteurs</option>
            <option value="6">Apologie de comportements illégaux</option>
            <option value="9">Données personnelles</option>
          </optgroup>
          <optgroup label="Contenus interdits">
            <option value="7">Pornographie</option>
            <option value="8">Insultes</option>
            <option value="10">Flood de masse</option>
            <option value="11">Raids, mass-dislikes et attaques de sites</option>
            <option value="16">Spoilers</option>
          </optgroup>
          <optgroup label="Contenus à modérer">
            <option value="12">Message inopportun</option>
            <option value="13">Doublons</option>
            <option value="14">Publicité</option>
            <option value="18">Règles spécifiques à chaque forum</option>
          </optgroup>
        </select>
      </div>

      <div class="form__block">
        <textarea class="form__textarea" name="rationale" placeholder="Raison du kick" tabindex="2"></textarea>
      </div>

      <input type="hidden" name="referer" value="<?= $referer ?>">
      <input type="hidden" name="message_id" value="<?= $message_id ?>">

      <div class="form__block">
        <input class="button button--raised button--danger button--large button--scale" type="submit" value="Kicker" tabindex="3">
      </div>
    </form>

    <div class="back-button-container">
      <a class="button" href="<?= isset($kick_result) ? $referer : 'javascript:history.back()' ?>">Retour</a>
    </div>
    <div style="height: 1px"><!-- Hack for Safari iOS, otherwise no margin at the bottom --></div>
  </div>
</div>
