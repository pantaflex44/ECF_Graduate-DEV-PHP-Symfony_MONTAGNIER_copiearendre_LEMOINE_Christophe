<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Exception;

/* La classe DebugMiddleware vérifie si la variable d'environnement DEBUG est définie sur true et ajoute un attribut de débogage à l'objet de requête. */
class DebugMiddleware
{
    /**
     * Cette fonction PHP vérifie si la variable d'environnement 'DEBUG' est définie sur true et ajoute un attribut 'debug' à l'objet de requête.
     *
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * @param RequestHandler handler  est une instance de l'interface RequestHandler, qui est chargée de gérer la requête entrante et de renvoyer une réponse. Il contient la logique de traitement de la demande et de génération de la réponse.
     *
     * @return Response Un objet `Response` est renvoyé.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $debug = in_array('DEBUG', array_keys($_ENV)) && strtolower($_ENV['DEBUG']) === 'true';
        return $handler->handle($request->withAttribute('debug', $debug));
    }

}
