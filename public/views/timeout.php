<div class="sheet">
  <?php include '_header.php' ?>

  <div class="timeout">
    <h3 class="timeout-title"><?= $title ?></h3>

    <p class="timeout-text"><?= $message ?></p>

    <p>
      <a id="retry_url" class="button button--raised button--cta button--large" href="<?= $_SERVER['REQUEST_URI'] ?>" data-no-instant>Réessayer</a>
      <script>document.getElementById('retry_url').href = location.href</script>
    </p>
  </div>
</div>
