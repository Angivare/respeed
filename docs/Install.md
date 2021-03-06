Ce document détaille l’installation d’une version de développement de JVForum sous Windows.

# Git

## Installation

[Téléchargez Git](https://git-scm.com/download/win) et lancez l’installation. Cliquez sur suivant quelques millions de fois jusqu’à ce que l’installation se fasse. Git est maintenant installé.

## Téléchargement de JVForum

Créez le dossier où sera stocké votre version de développement de JVForum. Ouvrez ce dossier, et faites un clic droit. Cliquez sur « Git Bash Here ». Attendez que du texte s’affiche.

Copiez `git clone https://github.com/dieulot/jvforum.git .` dans votre presse-papier. Faites un clic-droit dans la console et collez-le. Appuyez sur entrée, puis attendez que le repo se télécharge.

Votre version de développement est maintenant installée.

# WAMP

Note si vous utilisez Skype : Par défaut, Skype est en conflit avec WAMP car il utilise le même port qu’Apache. Pour régler ça, rendez-vous dans la barre de menu de Skype dans Outils → Options… → Avancées → Connexion et décochez « Utiliser les ports 80 et 443 pour les connexions entrantes supplémentaires », puis quittez Skype (et relancez-le si vous voulez).

Rendez-vous sur [le site de WAMP](http://www.wampserver.com/#download-wrapper) et téléchargez la version 32 bits (en haut du formulaire qui s’affiche, cliquez sur « passer au téléchargement direct ») puis installez la.

Quand le pare-feu Windows vous parle d’Apache, décochez « réseaux publics », cochez « réseaux privés » et validez.

Une fois installé, rendez-vous sur [127.0.0.1](http://127.0.0.1/) pour confirmer que l’installation s’est bien passé. Une page de WAMP devrait s’afficher.

## Configuration d’Apache

### Activation du module d’URL rewrite

Dans la zone de notification Windows, faites un clic gauche sur l’icône de WAMP. Allez dans Apache > Modules Apache, et cochez `rewrite_module`.

### Ajout d’un virtual host

Ouvrez `C:\wamp\bin\apache\apache2.4.9\conf\httpd.conf` avec [Notepad++](https://notepad-plus-plus.org/download/v6.8.6.html) (le bloc-notes Windows ne gère pas les retours à la ligne Unix contenus dans le fichier), enlevez le # au début de la ligne `#IncludeOptional "c:/wamp/vhosts/*"` qui se trouve vers la fin du fichier et sauvegardez.

Rendez-vous dans `C:\wamp\vhosts` et créez un nouveau fichier nommé `jvforum`, ouvrez-le et mettez dedans :

```
<VirtualHost dev.jvforum.fr:80>
	DocumentRoot "C:\Sites\jvforum\public"
	ServerName dev.jvforum.fr
	<Directory />
		Require local
		AllowOverride All
	</Directory>
</VirtualHost>
```

En remplaçant `C:\Sites\jvforum\public` par le chemin du dossier `public` à l’intérieur du dossier où vous avez `git pull` JVForum.

Cliquez sur l’icône de WAMP, puis sur « Redémarrez tous les services ».

Rendez-vous sur [dev.jvforum.fr](http://dev.jvforum.fr/). La page de présentation de JVForum devrait s’afficher.

Si vous obtenez une erreur, regardez ce que vous dit le fichier `C:\wamp\logs\apache_error.log`.

Vous rendre sur la page de connexion vous affichera une erreur, c’est parce que MySQL n’est pas encore configuré.

## Configuration de MySQL

### Importation du schéma des données

Rendez-vous sur [PhpMyAdmin](http://localhost/phpmyadmin/). En haut à gauche, cliquez sur « Nouvelle base de données », appelez-la `jvforum` et cliquez sur « Créer ».

Dans la liste des bases de données, cliquez sur `jvforum`, rendez-vous dans l’onglet « SQL », puis copiez le contenu du fichier `schema.sql` dans le champ de texte, et cliquez sur « Exécuter » à droite.

### Configuration JVForum

Faites une copie du fichier `config.sample.php` et appelez-la `config.php`.

***

Votre version de développement de JVForum est maintenant prête à être utiliser, vous pouvez désormais [vous connecter](http://dev.jvforum.fr/connexion).
