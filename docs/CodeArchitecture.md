# Architecture du code

## /

- `generate_id_key.php` est un script pour g�n�rer une `ID_KEY` utilis�e dans `config.php`

Le dossier `cron` contient des scripts � ex�cuter via cron. Ils sont seulement utiles pour JVForum en production, vous n�avez pas besoin de vous en pr�occuper.

Le dossier `docs` contient de la documentation.

Le dossier `php-encryption` contient le code de [php-encryption](https://github.com/defuse/php-encryption), une classe pour faire du chiffrage sym�trique.

Le dossier `public` contient le plus int�ressant�: tous les fichiers expos�s sur le web.

## /public

`index.php` est le fichier qui g�re l�affichage des pages.

`Auth.php`, `Db.php` et `Jvc.php` contiennent les classes `Auth`, `Db` et `Jvc`. `Auth` s�occupe de la gestion des tokens pour �viter le CSRF. `Db` contient les fonctions qui interagissent avec la base de donn�es. `Jvc` contient les fonctions qui interagissent avec JVC.

`salespage.php` est la page de pr�sentation de JVForum. `connexion.php` la page de connexion. Ces deux pages sont celles accessibles en �tant non-connect�e, et ne passent pas par `index.php`. `deconnexion` aussi ne passe pas par `index.php`.

`helpers.php` est le fichier contenant des fonctions diverses, un peu fourre-tout.

`parser.php` contient les fonctions pour r�cup�rer et parser les forums, topics et CDVs.

`collect_icstats.php` est un script pour r�cup�rer des stats en rapport avec InstantClick. `stats.php` est le fichier qui montre le nombre de messages post�s chaque jour. Vous n�avez pas besoin de vous en pr�occuper.

## /public/scripts

Contient les scripts JavaScript utilis�s.

Scripts de tierce-partie�:

- jQuery est une biblioth�que bien connu pour faciliter des op�rations courantes en JavaScript.
- FastClick permet de supprimer le d�lai entre le toucher et l�affichage de la page sur iOS et Android.
- InstantClick (`instantclick.js` ainsi que `loading-indicator.js`) s�occupe du pr�chargement des pages. J�en suis le d�veloppeur, JVForum est utilis� comme projet r�el pour l�am�liorer.

JVCode est le parser du HTML des messages en JVcode, pour permettre aux fonctions citer et modifier d��tre instantan�es.

`app.js` est le fichier JavaScript principal.

## /public/ajax

Contient les scripts appel�s via Ajax.

## /public/pages

Contient les pages affich�es lorsque l�on est connect�.

Exceptions�: `_header.php` est le header, `layout.php` est le squelette des pages. `forum_pagination.php` est un morceau de page inclue dans `forum.php`.
