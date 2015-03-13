Installez uWamp (c'est un serveur wamp portable)

Téléchargez votre repo git dans uWamp/www/
Vous aurez ainsi ce genre de répertoires: uWamp/www/respeed/public

Pour configurer le vhost, cliquez sur 'Apache Config' dans la fenêtre de uWamp,
ajoutez un virtual server à gauche (bouton '+'), mettez 'respeed.dev' en server
name et '{DOCUMENTPATH}/respeed/public' en document root. Validez en cliqant sur OK

Il vous suffit maintenant d'aller sur http://respeed.dev/ pour tester votre fork de
respeed.