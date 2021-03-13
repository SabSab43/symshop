# Symshop
Un prototype de boutique en ligne réalisé avec Symfony 5 en cours de réalisation.


Pour télécharger le projet:

    git clone https://github.com/SabSab43/symshop.git


Pour installer les dépendances (nécessite Composer):

    composer install
    
    
# Configuration des varables d'environnement:

Définissez vos variables d'environnement directement sur votre système ou bien dans un fichier .env, exemple: 

    .env.local

Vous devez définir les variaibles d'environnement suivantes avec vos informations:

    APP_SECRET=VOTRE_CLE_SECRETE

    DATABASE_URL="mysql://DB_USER:DB_PASSWORD@DB_ADDRESS:DB_PORT/DB_NAME?serverVersion=5.7"

    MAILER_DSN=smtp://USER:PASSWORD@ADDRESS:PORT?encryption=ENCRYPTION&auth_mode=AUTH_MOD

    STRIPE_SECRET_KEY="VOTRE_CLE_SECRETE"

    STRIPE_PUBLIC_KEY="VOTRE_CLE_PUBLIC"
    
# Initialiser la base de données:

Une fois le fichier .env configuré, vous pouvez exécuter ces commandes pour créer la base de données et ses tables:

    php bin/console doctrine:database:create
    
    php bin/console doctrine:migrations:migrate --no-interaction
    
# Alimenter la base de données:
 
 Avant d'alimenter la base de données, pensez à changer les identifiants de l'administrateur et des utilisateurs dans le fichier suivant:
 
     ..\symshop\boutique\src\DataFixtures\AppFixtures.php

Lancez ensuite la commande suivante:
    
    php bin/console d:f:l --no-interaction  

 # Lancer le serveur
 
Vous pouvez maintenant lancer le sevreur en entrant la commande suivante (veillez à bien être dans le répertoire du projet):

    symfony serve