# Whis Helper
WishMatch is a web application designed to help users manage personal wishlists and discover meaningful gift ideas based on shared interests and categories.

## Alpha
The Alpha version focuses on building a stable technical foundation for future development. At this stage, the application implements a secure authentication system, a structured domain model, and a fully functional API layer prepared for wishlist management and matching logic.
This phase prioritizes architecture, security, and extensibility over advanced integrations.

# CHANGELOG.md
Look at the CHANGELOG.md to see what has been added!

# First start
run DockerDesktop
run in command line next commands:
* `docker-compose up -d --build` - second time use `docker-compose up`
* `docker-compose exec php /bin/bash`
* `symfony check:requirements`
* `composer install`
* `yarn install` to install yarn and nodejs if not exists
* `yarn run watch` to compile assets whenever changes are made
* `yarn run dev` to compile assets once
* Open: http://localhost:8080/
* For api open: http://localhost:8080/api
* For docs open: http://localhost:8080/api/docs

# Better performance
* for performance issue with WSL2 in docker-desktop vendor directory is not in volumes, you have to set volumes in php container as below and run composer install in container!
  *  `- /var/www/project/vendor # ignore vendor folder`
* also remember to disable XDEBUG - look at .env file

## prepare database
* `php bin/console doctrine:database:create` to create database on docker-compose basics
* `php bin/console doctrine:migration:migrate`
* `php bin/console doctrine:fixtures:load` to fill the database

additionally after changes run `symfony console make:migration` and then `symfony console doctrine:migration:migrate`

### link database to phpstorm:
We have to create new data source with mysql. Then in properties we added some config:
* Host: `localhost`
* Port: `4306`
* User: `wh_admin`
* Password `wh_tester`
* Database `wh_database`

## In main directory we create docker-compose.yml file to config our containers for MySql, PHP and Nginx
### PHP
we create docker/php directory with Dockerfile with configuration where we:
* PHP  is mapped on - '9000:9000'
* Install the PHP extensions Symfony depends on.
* Set the working directory of the container to /var/www/project
* Install composer
* Install the Symfony CLI
* Installed npm, nodejs, yarn for encore
### Nginx
* We create nginx container that is mapped on - '8080:80'
* Nginx container has configuration in directory docker/nginx where we add listening on 80, 
* we also set index of our symfony project (index.php), server_name "localhost" and root for public: /var/www/project/public;
### MySql
* MySql in version 8.0, with pass and database env.
* MySql is mapped on - '4306:3306'
### php-stan and php-cs-fixer:
* we added phpstan/phpstan and friendsofphp/php-cs-fixer to dev require in composer.json with scripts
* remember to copy phpstan.neon and .php-cs-fixer.dist.php without dist part in names.
* to run phpstan: `composer quality` (possible add path after quality to check only specific directory)
* to run php-cs-fixer: `composer style-fix src --dry-run` (remove --dry-run to fix issues, also it is possible to add path to check/fix only specific directory)
* if a memory issue with phpstan occurs you can increase memory limit by changing --memory-limit in composer.json
* phpstan level is set to 6
