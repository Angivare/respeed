<?php

$topic_id = (int)$_GET['lock'];
$unlock = isset($_GET['unlock']);

if (!$jvc->logged_into_moderation) {
  header('Location: /moderation');
  exit;
}

$referer = isset($_SERVER['HTTP_REFERER']) ? h($_SERVER['HTTP_REFERER']) : '/';

if ($unlock) {
  $unlock_action = $jvc->unlock($topic_id);
  set_toast_for_next_page($unlock_action ? 'Topic déverouillé' : ('Erreur déverouillage : ' . $jvc->err()));
  header('Location: ' . $referer);
  exit;
}

if (isset($_POST['rationale'], $_POST['referer'])) {
  $rationale = $_POST['rationale'];
  $referer = h($_POST['referer']);
  $lock_action = $jvc->lock($topic_id, $rationale);
  if ($lock_action) {
    set_toast_for_next_page('Topic verrouillé');
    header('Location: ' . $referer);
    exit;
  }
  else {
    $error = $jvc->err();
  }
}

$title = 'Verrouiller topic';
?>
<div class="sheet">
  <div class="content no-menu">
    <h1 class="page-title"><?= $title ?></h1>

    <form class="form" action="/lock/<?= $topic_id ?>" method="post">
<?php if (isset($error)): ?>
      <div class="form__block">
        <div class="form__error"><?= $error ?></div>
      </div>
<?php endif ?>

      <div class="form_block">
        <textarea class="form__textarea" name="rationale" placeholder="Facultatif : raison du verrouillage" tabindex="2"></textarea>
      </div>

      <input type="hidden" name="referer" value="<?= $referer ?>">

      <div class="form_block">
        <input class="button button--raised button--danger button--large button--scale" type="submit" value="Verrouiller" tabindex="3">
      </div>
    </form>

    <div class="back-button-container">
      <a class="button" href="<?= isset($punish_result) ? $referer : 'javascript:history.back()' ?>">Retour</a>
    </div>
    <div style="height: 1px"><!-- Hack for Safari iOS, otherwise no margin at the bottom --></div>
  </div>
</div>
