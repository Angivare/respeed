Installation avec UwAmp (windows)
=================================

Installez UwAmp http://www.uwamp.com/

Téléchargez votre repo git dans UwAmp/www/
Vous aurez normalement ce répertoire: UwAmp/www/respeed/public

Pour configurer le vhost, cliquez sur 'Apache Config' dans la fenêtre de UwAmp,
ajoutez un virtual server à gauche (bouton '+'), mettez 'respeed.dev' en server
name et '{DOCUMENTPATH}/respeed/public' en document root. Validez en cliqant sur OK

Il vous suffit maintenant d'aller sur http://respeed.dev/ pour tester votre fork de
respeed.