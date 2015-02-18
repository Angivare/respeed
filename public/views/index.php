<?php
$title = 'Respeed';

ob_start();
?>
<h3>Forums préférés</h3>
<ul>
  <li><a href="?forum=1000021&amp;forum_slug=communaute">Communauté</a>
</ul>

<?php
$body = ob_get_contents();
ob_end_clean();
