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
    
    USER_PASSWORD="P@ssw0rd"
    
    ADMIN_PASSWORD="AdminP@ssw0rd"

    NB_FORWARD_PRODUCTS=3
    
# Initialiser la base de données:

Une fois le fichier .env configuré, vous pouvez exécuter ces commandes pour créer la base de données et ses tables:

    php bin/console doctrine:database:create
    
    php bin/console doctrine:migrations:migrate --no-interaction
    
# Alimenter la base de données:
 
 En fonction de votre configuration serveur, vous devrez créer ou non la base de données avec la commande suivante:
    
    php bin/console doctrine:database:create
 
 Le fichier composer.json contient un script "database-setup" qui créé les tables et les remplit.
 
 Vous pouvez configurer les paramètres des fixtures dans le fichier "config/services.yaml" (l.70)
 
Les commandes pour créér et alimenter les tables de la base de donénes mannuellement:
    php bin/console d:m:m --no-interaction
    php bin/console d:f:l --no-interaction  

 # Lancer le serveur
 
Vous pouvez maintenant lancer le serveur en entrant la commande suivante (veillez à bien être dans le répertoire du projet):

    symfony serve
