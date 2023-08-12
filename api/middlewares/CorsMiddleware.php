<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;


/* La classe CorsMiddleware est un middleware PHP qui ajoute des en-têtes CORS à la réponse. */

class CorsMiddleware
{

    /**
     * La fonction ci-dessus est un middleware PHP qui ajoute des en-têtes CORS à la réponse.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite à votre application. Il contient des informations telles que la méthode de requête, les en-têtes et le corps.
     * @param RequestHandler handler Le paramètre `` est une instance de l'interface `RequestHandler`. Il représente le middleware suivant ou le gestionnaire final qui traitera la requête et renverra une réponse.
     * 
     * @return Response un objet Réponse.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        header('Access-Control-Allow-Origin: *');
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}
