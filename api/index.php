<?php


/* Ce bloc de code est responsable du chargement automatique de tous les fichiers nécessaires à l'exécution de l'application. */
require __DIR__ . '/vendor/autoload.php';
foreach (['libs', 'middlewares', 'controllers'] as $dir) {
    $path = sprintf("%s/%s", __DIR__, $dir);
    foreach (scandir($path) as $file) {
        $filepath = sprintf("%s/%s", $path, $file);
        if (is_file($filepath)) {
            require $filepath;
        }
    }
}

/* Ces lignes de code importent des classes du standard PSR-7 pour les messages HTTP et du framework Slim. Plus précisément, `use Psr\Http\Message\ResponseInterface as Response` et `use Psr\Http\Message\ServerRequestInterface as Request` importent les interfaces `ResponseInterface` et `ServerRequestInterface` de la norme PSR-7, respectivement, et les aliasent comme `Réponse` et `Requête`. Cela permet une utilisation plus facile et plus lisible de ces interfaces tout au long du code. `use Slim\Factory\AppFactory` importe la classe `AppFactory` du framework Slim, qui est utilisée pour créer une nouvelle instance d'application Slim. */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

/* Charge les variables d'environnement à partir d'un fichier `.env` situé dans le répertoire parent du répertoire courant. La classe `DotEnvEnvironment` est responsable de l'analyse du fichier `.env` et de la définition des variables d'environnement en tant que paires clé-valeur dans le tableau superglobal ``. Cela permet à l'application d'accéder à des informations sensibles telles que les informations d'identification de la base de données ou les clés API sans les coder en dur dans le code. */

(new App\Libs\DotEnvEnvironment())->load(__DIR__ . '/..');

/* Cette ligne de code vérifie si la variable d'environnement `DEBUG` est définie sur `true`. Il vérifie d'abord si la clé `'DEBUG'` est présente dans le tableau des clés des variables d'environnement en utilisant `array_keys()`. S'il est présent, il vérifie si la valeur correspondante est égale à la chaîne `'true'` (insensible à la casse) en utilisant `strtolower(['DEBUG']) === 'true'`. Si les deux conditions sont vraies, il définit la variable `` sur `true`, indiquant que l'application s'exécute en mode débogage. */
$debug = in_array('DEBUG', array_keys($_ENV)) && strtolower($_ENV['DEBUG']) === 'true';

/* Créé une nouvelle instance de l'application en utilisant la classe `AppFactory` du framework Slim. Cette instance sera utilisée pour définir les routes et le middleware de l'application, et pour gérer les requêtes HTTP entrantes. */
$app = AppFactory::create();

/* La méthode `->addRoutingMiddleware();` ajoute le middleware de routage à l'application. */
$app->addRoutingMiddleware();
/* La ligne ` = ->addErrorMiddleware(, true, true);` crée un middleware d'erreur pour l'application. */
$errorMiddleware = $app->addErrorMiddleware($debug, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
/* La ligne `->forceContentType('application/json');` définit le type de contenu de la réponse d'erreur à `application/json`. Cela signifie que lorsqu'une erreur se produit dans l'application et qu'une réponse d'erreur est générée, l'en-tête de type de contenu de la réponse est défini sur "application/json", indiquant que le corps de la réponse sera au format JSON. Ceci est utile lors de la création d'API, car cela permet aux clients d'analyser et de gérer facilement la réponse d'erreur. */
$errorHandler->forceContentType('application/json');

/* La ligne `->add(new \App\Middlewares\DebugMiddleware());` ajoute une nouvelle instance de la classe `DebugMiddleware` à la pile middleware de l'application. */
$app->add(new \App\Middlewares\DebugMiddleware());
/* La ligne `->add(new \App\Middlewares\DbMiddleware());` ajoute une nouvelle instance de la classe `DbMiddleware` à la pile middleware de l'application. Ce middleware est responsable de l'établissement d'une connexion à la base de données et de sa mise à disposition des middleware et des gestionnaires de routage ultérieurs. Il s'assure que la connexion à la base de données est établie avant de traiter la demande et ferme la connexion après l'envoi de la réponse. */
$app->add(new \App\Middlewares\DbMiddleware());
/* La ligne `->add(new \App\Middlewares\GzipMiddleware());` ajoute une nouvelle instance de la classe `GzipMiddleware` à la pile middleware de l'application. Ce middleware est chargé de compresser le corps de la réponse à l'aide de l'algorithme de compression Gzip. Il compresse automatiquement le corps de la réponse si le client prend en charge la compression Gzip et définit les en-têtes appropriés dans la réponse. Cela permet de réduire la taille de la réponse et d'améliorer les performances de l'application en réduisant la quantité de données devant être transférées sur le réseau. */
$app->add(new \App\Middlewares\GzipMiddleware());
/* La ligne `->add(new \App\Middlewares\CorsMiddleware());` ajoute une nouvelle instance de la classe `CorsMiddleware` à la pile middleware de l'application. Ce middleware est responsable du traitement des requêtes Cross-Origin Resource Sharing (CORS). CORS est un mécanisme qui permet aux ressources restreintes d'une page Web d'être demandées à un autre domaine en dehors du domaine d'où provient la ressource. La classe `CorsMiddleware` ajoute les en-têtes nécessaires à la réponse pour autoriser les requêtes d'origine croisée, telles que l'autorisation de certaines méthodes HTTP, en-têtes et origines. Il permet de contrôler et de sécuriser la communication entre le client et le serveur lors de requêtes cross-origin. */
$app->add(new \App\Middlewares\CorsMiddleware());

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

/* Ce code configure une route pour la méthode GET au point de terminaison `/api`. Lorsqu'une requête GET est envoyée à ce point de terminaison, la fonction transmise en deuxième argument est exécutée. Cette fonction prend un objet `Request` et un objet `Response` comme arguments. Il écrit une chaîne dans le corps de la réponse et renvoie l'objet de réponse. La chaîne écrite dans le corps de la réponse est un message indiquant la version et la paternité de l'API Garage V. Parrot. */
$app->get('/api', function (Request $request, Response $response) {
    $response->getBody()->write("Garage V. Parrot API v0.1 by Christophe LEMOINE - Copyright (c)2023");
    return $response;
});

/* Ce code définit un tableau qui contient des informations sur les différentes routes que l'application va gérer. Chaque élément du tableau représente une route et contient la méthode HTTP, le chemin du point de terminaison, la méthode du contrôleur qui gérera la requête et tous les middlewares qui doivent être appliqués à la route. */
$routes = [
    ['method' => 'post',    'path' => 'login',                                      'controller' => 'AuthController:login',                 'middlewares' => []],
    ['method' => 'get',     'path' => 'refresh',                                    'controller' => 'AuthController:refresh',               'middlewares' => []],
    ['method' => 'get',     'path' => 'logout',                                     'controller' => 'AuthController:logout',                'middlewares' => []],

    ['method' => 'get',     'path' => 'users',                                      'controller' => 'UsersController:list',                 'middlewares' => []],
    ['method' => 'post',    'path' => 'user_exists',                                'controller' => 'UsersController:exists',               'middlewares' => []],
    ['method' => 'post',    'path' => 'add_user',                                   'controller' => 'UsersController:add',                  'middlewares' => []],
    ['method' => 'post',    'path' => 'update_user/{id:\d+}',                       'controller' => 'UsersController:update',               'middlewares' => []],
    ['method' => 'delete',  'path' => 'delete_user/{id:\d+}',                       'controller' => 'UsersController:delete',               'middlewares' => []],
    ['method' => 'post',    'path' => 'activate_user/{id:\d+}',                     'controller' => 'UsersController:activate',             'middlewares' => []],
    ['method' => 'post',    'path' => 'change_user_password/{id:\d+}',              'controller' => 'UsersController:password',             'middlewares' => []],
    ['method' => 'get',     'path' => 'reset_user_password/{id:\d+}',               'controller' => 'UsersController:reset',                'middlewares' => []],

    ['method' => 'get',     'path' => 'services',                                   'controller' => 'ServicesController:list',              'middlewares' => []],
    ['method' => 'post',    'path' => 'add_service',                                'controller' => 'ServicesController:add',               'middlewares' => []],
    ['method' => 'post',    'path' => 'update_service/{id:\d+}',                    'controller' => 'ServicesController:update',            'middlewares' => []],
    ['method' => 'delete',  'path' => 'delete_service/{id:\d+}',                    'controller' => 'ServicesController:delete',            'middlewares' => []],

    ['method' => 'get',     'path' => 'openings',                                   'controller' => 'OpeningHoursController:list',          'middlewares' => []],
    ['method' => 'post',    'path' => 'add_period',                                 'controller' => 'OpeningHoursController:add',           'middlewares' => []],
    ['method' => 'post',    'path' => 'update_period/{id:\d+}',                     'controller' => 'OpeningHoursController:update',        'middlewares' => []],
    ['method' => 'delete',  'path' => 'delete_period/{id:\d+}',                     'controller' => 'OpeningHoursController:delete',        'middlewares' => []],

    ['method' => 'get',     'path' => 'comments',                                   'controller' => 'CommentsController:list',              'middlewares' => []],
    ['method' => 'get',     'path' => 'approved_comments',                          'controller' => 'CommentsController:approved_list',     'middlewares' => []],
    ['method' => 'post',    'path' => 'add_comment',                                'controller' => 'CommentsController:add',               'middlewares' => []],
    ['method' => 'delete',  'path' => 'delete_comment/{id:\d+}',                    'controller' => 'CommentsController:delete',            'middlewares' => []],

    ['method' => 'get',     'path' => 'image/{id}/{file}[/{w:\d+}[/{h:\d+}]]',      'controller' => 'OffersController:get_image',           'middlewares' => []],
    ['method' => 'post',    'path' => 'offers[/{page}[/{per_page}]]',               'controller' => 'OffersController:list',                'middlewares' => []],
    ['method' => 'get',     'path' => 'filters_limits',                             'controller' => 'OffersController:filters_limits',      'middlewares' => []],
];
foreach ($routes as $route) {
    $r = $app->map(
        [strtoupper($route['method'])],
        sprintf('/api/%s', $route['path']),
        sprintf('App\Controllers\%s', $route['controller'])
    );
    $m = array_merge(['AuthMiddleware'], $route['middlewares']);
    foreach ($m as $middleware) {
        $r = $r->add(sprintf('App\Middlewares\%s', $middleware));
    }
}

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

/* Démarre l'application et gère la requête HTTP entrante en la faisant correspondre à la route appropriée et en exécutant la méthode de contrôleur correspondante. Il renvoie également la réponse au client. */
$app->run();
