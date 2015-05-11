# JVForum

JVForum a besoin d’être documenté avant de pouvoir accepter les contributions. Revenez dans quelques jours. (Écrit le 11 mai.)

.

.

.

.

.

## Prérequis

- PHP ≥ 5.4
- cURL

## Participer au projet

Pour participer au projet vous devrez utiliser Git, le logiciel de révision de version le plus en vogue des dernières années. Apprendre Git vous apportera [bien des choses](http://stackoverflow.com/questions/1408450/why-should-i-use-version-control).

Si vous ne l’avez pas encore appris, la meilleure intro de Git qui soit est [Git Internals](https://github.com/pluralsight/git-internals-pdf/releases). En plus des bases couvertes dedans, deux choses utile à savoir est que 1) `git add -p <fichier>` vous permettra de ne faire un commit qu’avec certaines lignes, 2) voici [comment revenir en arrière](http://stackoverflow.com/questions/927358/how-to-undo-the-last-commit)

Pour plus de détails sur comment participer, voir le fichier [Contributing](Contributing.md).

## Liste de choses à faire

Si vous comptez participer à quelque chose, ou si vous voulez des détails sur quelque chose, signalez-le sur [issue appropriée], ainsi les autres pourront voir si quelqu’un est déjà en train de travailler et de faire des progrès sur une partie.

Ne cherchez pas à faire parfait. Des petites contributions vallent mieux que des grosses qui ne sont jamais complétées. Si vous n’êtes pas sûr d’avoir bien fait les choses ce n’est pas grave, proposez votre PR.

### Moyennement important

#### Système de lightbox pour les CDV et/ou DDB (JS, CSS)

Les CDV et DDB sont prévues et il leur faut la même chose : Ouvrir une page normale sur mobile, et dans une lightbox sur desktop (utilisez `isBigScreen`, si c’est mobile (`false`) ne faites rien, si c’est desktop (`true`) chargez la page (une page de test) et affichez-la dans une lightbox)

La lightbox ne doit pas griser ou rendre innaccessible le reste de la page derrière, et cliquer sur le reste de la page doit fermer la lightbox.

Sur desktop, il n’y a pas besoin de s’occuper de changer l’URL de la page (avec `history.pushState`). Sur mobile elles sont là de base.

#### Refactoring

Si vous voyez des choses qui ont besoin

### Bientôt

Une liste de ce qu’il y aura à contribuer plus tard, mais pas de suite car je n’ai pas encore réfléchi au design nécessaire.

#### Lecture/envoi MP (PHP)

## License

Le projet n’est pas sous license libre à cause des icônes Material Design qui sont sous CC-BY, ce qui je crois ne peut se mélanger avec une license MIT/GPL, et parce que dans le futur il pourrait y avoir des smileys supplémentaires dans JVForum qui eux non plus ne seront pas redistribuables sous license libre.
