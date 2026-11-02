# Déployer PompFlow sur Railway

Guide pour une version de démonstration en ligne (HTTPS inclus). Les étapes ont été écrites
sans pouvoir tester Railway directement : si un écran diffère, suis l'intention de l'étape.
Les tarifs et offres gratuites changent : vérifie-les sur le site de Railway avant de commencer.

## 0. Avant de commencer

- Les tests sont verts : `php artisan test`
- `.env` n'est pas suivi par git : `git ls-files | grep "^.env"` ne doit rien afficher
- Tout est commité et poussé sur GitHub (branche `main`)

## 1. Ajouter les fichiers de déploiement au projet

Copie à la racine : `Dockerfile`, `.dockerignore`, `docker/start.sh`,
`app/Console/Commands/InitialiserApplication.php`, `tests/Feature/InitialiserTest.php`.

Dans `bootstrap/app.php`, fais confiance au proxy de Railway (sinon les liens et les formulaires
sont générés en `http://` au lieu de `https://`) :

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
    // ... tes autres lignes éventuelles
})
```

Puis : `php artisan test`, commit, `git push`.

## 2. Créer le projet Railway

1. Crée un compte sur railway.com avec GitHub.
2. **New Project** → **Deploy from GitHub repo** → choisis `PompFlow`. Railway détecte le `Dockerfile`.
3. Dans le même projet : **+ New** → **Database** → **MySQL**.

## 3. Variables d'environnement

Dans le service PompFlow → **Variables** → colle (en adaptant) :

```
APP_NAME=PompFlow
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # sortie de: php artisan key:generate --show
APP_URL=https://TON-DOMAINE   # voir étape 4
APP_LOCALE=fr
APP_TIMEZONE=Africa/Conakry

LOG_CHANNEL=stderr
SESSION_DRIVER=database
SESSION_LIFETIME=43200
CACHE_STORE=database
QUEUE_CONNECTION=sync

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SUPER_ADMIN_EMAIL=ton.email@exemple.com
SUPER_ADMIN_PASSWORD=un-mot-de-passe-de-12-caracteres-minimum
SUPER_ADMIN_NAME=Barry Mamadou Moussa
```

Les `${{MySQL.…}}` sont des références : « MySQL » doit être le nom exact du service base de données.

**Emails** (invitations, mot de passe) : ajoute les variables SMTP.
- Démo rapide : ton Gmail avec mot de passe d'application (limité en volume) :
  `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`,
  `MAIL_USERNAME` et `MAIL_FROM_ADDRESS` = ton adresse, `MAIL_PASSWORD` = mot de passe d'application.
- Plus propre : un service dédié (Brevo, Resend, Mailgun) avec leurs identifiants SMTP.

## 4. Domaine public

Service PompFlow → **Settings** → **Networking** → **Generate Domain**.
Copie l'adresse `https://….up.railway.app` dans `APP_URL`, puis redéploie.

## 5. Premier démarrage

À chaque démarrage, `docker/start.sh` fait : migrations → création du super admin (s'il n'existe pas)
→ cache de configuration et de vues → lancement du serveur.

Dans **Deployments** → **View logs**, tu dois voir `Super admin créé : …` puis le serveur démarrer.

**Ensuite, supprime la variable `SUPER_ADMIN_PASSWORD`** (le compte est créé, le mot de passe
n'a plus à rester dans l'environnement).

## 6. Vérifications

- `https://TON-DOMAINE` affiche l'accueil avec le logo.
- Connexion super admin sur `/admin` ; création d'une société et d'un responsable.
- L'email d'invitation arrive et le lien ouvre bien la page de mot de passe en `https://`.
- Création d'un véhicule, d'une course, d'un chauffeur ; test de l'espace chauffeur sur téléphone.
- Facture PDF téléchargeable.

## 7. Installer l'application sur un téléphone

Voir la PWA : sur Android (Chrome) menu ⋮ → « Installer l'application » ; sur iPhone (Safari)
Partager → « Sur l'écran d'accueil ».

## 8. Mises à jour

`git push` sur `main` : Railway reconstruit et redéploie automatiquement. Les migrations
s'exécutent au démarrage.

## 9. Sauvegardes et limites à connaître

- Le disque du conteneur est **éphémère** : ne stocke rien d'important dans `storage/`
  (aucun fichier envoyé par les utilisateurs pour l'instant, les factures PDF sont générées à la volée).
- La base de données est le seul élément à sauvegarder : exporte-la régulièrement
  (`mysqldump`, ou les sauvegardes proposées par Railway selon ton offre).
- N'exécute **jamais** `DemoSeeder` en production.

## Dépannage

| Symptôme | Piste |
|---|---|
| Erreur 500 | Logs du service. Active `APP_DEBUG=true` quelques minutes seulement, puis remets `false`. |
| Page sans style | Le build des assets a échoué : lis les logs de build (étape `npm run build`). |
| Formulaires ou liens en `http://` | `trustProxies` oublié dans `bootstrap/app.php`, ou `APP_URL` incorrecte. |
| Déconnexion permanente | `APP_KEY` absente ou `SESSION_DRIVER` incorrect. Vérifie que la table `sessions` existe. |
| « Connection refused » base de données | Mauvais nom de service dans `${{MySQL.…}}`. |
| Emails non reçus | `MAIL_FROM_ADDRESS` doit être une adresse autorisée par le fournisseur SMTP. |

## Alternative : un VPS

Si tu préfères tout maîtriser (et c'est un bon exercice systèmes/réseaux) : un petit VPS
Ubuntu avec Nginx, PHP 8.4-FPM, MySQL, Certbot (HTTPS), un `cron` pour `php artisan schedule:run`
et Supervisor si tu ajoutes des files d'attente. Demande-moi un guide dédié si tu veux cette voie.
