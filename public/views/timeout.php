<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content content--timeout">
    <h3 class="page-title page-title--timeout"><?= $title ?></h3>

    <p><?= $message ?></p>

    <p>
      <a id="retry_url" class="button button--raised button--cta button--large" href="<?= $_SERVER['REQUEST_URI'] ?>" data-no-instant>Réessayer</a>
      <script>document.getElementById('retry_url').href = location.href</script>
    </p>
  </div>
</div>
