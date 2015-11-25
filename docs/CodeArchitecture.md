# Architecture du code

## /

- `generate_id_key.php` est un script pour générer une `ID_KEY` utilisée dans `config.php`

Le dossier `cron` contient des scripts à exécuter via cron. Ils sont seulement utiles pour JVForum en production, vous n’avez pas besoin de vous en préoccuper.

Le dossier `docs` contient de la documentation.

Le dossier `php-encryption` contient le code de [php-encryption](https://github.com/defuse/php-encryption), une classe pour faire du chiffrage symétrique.

Le dossier `public` contient le plus intéressant : tous les fichiers exposés sur le web.

## /public

`index.php` est le fichier qui gère l’affichage des pages.

`Auth.php`, `Db.php` et `Jvc.php` contiennent les classes `Auth`, `Db` et `Jvc`. `Auth` s’occupe de la gestion des tokens pour éviter le CSRF. `Db` contient les fonctions qui interagissent avec la base de données. `Jvc` contient les fonctions qui interagissent avec JVC.

`salespage.php` est la page de présentation de JVForum. `connexion.php` la page de connexion. Ces deux pages sont celles accessibles en étant non-connectée, et ne passent pas par `index.php`. `deconnexion` aussi ne passe pas par `index.php`.

`helpers.php` est le fichier contenant des fonctions diverses, un peu fourre-tout.

`parser.php` contient les fonctions pour récupérer et parser les forums, topics et CDVs.

`collect_icstats.php` est un script pour récupérer des stats en rapport avec InstantClick. `stats.php` est le fichier qui montre le nombre de messages postés chaque jour. Vous n’avez pas besoin de vous en préoccuper.

## /public/scripts

Contient les scripts JavaScript utilisés.

Scripts de tierce-partie :

- jQuery est une bibliothèque bien connu pour faciliter des opérations courantes en JavaScript.
- FastClick permet de supprimer le délai entre le toucher et l’affichage de la page sur iOS et Android.
- InstantClick (`instantclick.js` ainsi que `loading-indicator.js`) s’occupe du préchargement des pages. J’en suis le développeur, JVForum est utilisé comme projet réel pour l’améliorer.

JVCode est le parser du HTML des messages en JVcode, pour permettre aux fonctions citer et modifier d’être instantanées.

`app.js` est le fichier JavaScript principal.

## /public/ajax

Contient les scripts appelés via Ajax.

## /public/views

Contient les pages affichées lorsque l’on est connecté.

Exceptions : `_header.php` est le header, `layout.php` est le squelette des pages. `forum_pagination.php` est un morceau de page inclue dans `forum.php`.

