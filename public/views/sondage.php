<?php

$choice = $db->get_poll_vote(1, $jvc->user_id);

if ($choice == -1) {
  if (isset($_POST['choice'], $_POST['referer'], $_POST['comment'])) {
    $choice = (int)$_POST['choice'];
    $referer = h($_POST['referer']);
    $comment = h($_POST['comment']);
    $db->set_poll_vote(1, $jvc->user_id, $choice, $comment);
  }
}

if (!isset($referer)) {
  $referer = isset($_SERVER['HTTP_REFERER']) ? h($_SERVER['HTTP_REFERER']) : '/';
}

$title = 'Sondage : Aimeriez-vous un design sombre ?';
?>
<div class="sheet">
  <div class="content no-menu">
    <h1 class="page-title"><?= $title ?></h1>

<?php if ($choice == -1): ?>
    <form class="form" action="/sondage" method="post">
      <div class="form__block">
        <p class="form__text">Un design sombre a l’avantage de ne pas faire mal aux yeux le soir.</p>
        <p class="form__text">Il est question ici de <em>remplacer</em> le thème actuel par un sombre.</p>
      </div>

      <div class="form__block">
        <label class="form__choice">
          <input type="radio" name="choice" value="1"> Je préfèrerais garder un thème clair
        </label>
        <label class="form__choice">
          <input type="radio" name="choice" value="2"> J’aimerais un thème sombre
        </label>
        <label class="form__choice">
          <input type="radio" name="choice" value="3"> Je sais pas
        </label>
        <label class="form__choice">
          <input type="radio" name="choice" value="4"> Je m’en fiche
        </label>
      </div>

      <div class="form__block">
        <textarea class="form__textarea" name="comment" placeholder="Commentaire facultatif" tabindex="2"></textarea>
      </div>

      <input type="hidden" name="referer" value="<?= $referer ?>">

      <div class="form__block">
        <input class="button button--raised button--large button--scale" type="submit" value="Voter" tabindex="3">
      </div>
    </form>
<?php else: ?>
    <p>Merci de votre participation !</p>

    <div class="back-button-container">
      <a class="button button--raised button--large button--scale button--cta" href="<?= $referer ?>">Retour</a>
    </div>
    <div style="height: 1px"><!-- Hack for Safari iOS, otherwise no margin at the bottom --></div>
<?php endif ?>
  </div>
</div>
