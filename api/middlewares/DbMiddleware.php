<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use PDO;
use Exception;

/* La classe DbMiddleware établit une connexion à un serveur et une base de données MySQL, gère les exceptions et ajoute un attribut 'db' à l'objet Request avant de le transmettre au middleware suivant dans le pipeline. */
class DbMiddleware
{
    /**
     * Cette fonction établit une connexion à un serveur et une base de données MySQL, et gère les exceptions le cas échéant.
     *
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP reçue par le serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps. Dans ce code, l'objet  est utilisé pour transmettre des données au middleware suivant dans le pipeline en ajoutant un
     * @param RequestHandler handler  est une instance de l'interface RequestHandlerInterface, qui représente le middleware suivant ou le gestionnaire d'application final qui sera appelé après le middleware actuel. Il est chargé de traiter la demande entrante et de renvoyer une réponse. Dans ce code, la méthode ->handle() est appelée avec la modification
     *
     * @return le résultat de l'appel de la méthode `handle` sur l'objet ``, qui est passé en paramètre à la fonction, après avoir ajouté un attribut nommé `'db'` à l'objet ``.
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        try {
            $cs = sprintf('mysql:host=%s;port=%d;charset=utf8', $_ENV['MYSQL_HOST'], $_ENV['MYSQL_PORT']);

            $opts = [
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_FOUND_ROWS      => true,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES      => false,
                PDO::ATTR_STRINGIFY_FETCHES     => false,
            ];

            // MySQL server connection
            $pdo = new PDO($cs, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $opts);

            // create database if not exists
            $stmt = $pdo->query(sprintf("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s';", $_ENV['MYSQL_BASE']));
            $exists = (bool)$stmt->fetchColumn();
            if (!$exists) {
                $pdo->exec(sprintf("CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;", $_ENV['MYSQL_BASE']));
            }

            // use the database
            $pdo->exec(sprintf("USE %s;", $_ENV['MYSQL_BASE']));

            // if first run, load default SQL data
            if (in_array('FIRST_RUN', array_keys($_ENV)) && strtolower($_ENV['FIRST_RUN']) === 'true') {
                $filepath = __DIR__ . '/../../data.sql';
                if (file_exists($filepath)) {
                    $sql = file_get_contents($filepath);
                    $pdo->exec($sql);
                }
            }

            return $handler->handle($request->withAttribute('db', $pdo));
        } catch (Exception $ex) {
            var_dump($ex);
            return \App\Libs\SlimEx::send_error(
                500,
                "Erreur critique relative au serveur de données",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

}
