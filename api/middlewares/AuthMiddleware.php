<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Libs\User as User;

/* La classe AuthMiddleware vérifie si un jeton est valide et renvoie un objet utilisateur ou une réponse avec un code d'état de 401 ou 410 selon certaines conditions. */
class AuthMiddleware
{
    /**
     * Cette fonction vérifie si un jeton est valide et renvoie un objet utilisateur si c'est le cas.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param RequestHandler handler  est une instance de l'interface RequestHandler, qui représente le middleware suivant ou le gestionnaire final qui sera appelé dans la pile middleware. Il est chargé de traiter la demande en cours et de renvoyer une réponse.
     *
     * @return un objet Response avec un code d'état de 401 ou 410 si certaines conditions sont remplies, ou il renvoie le résultat de la méthode handle de l'objet RequestHandler passé en paramètre, avec un objet request mis à jour qui inclut un attribut 'user'.
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $user = null;

        try {
            $headers = [];
            foreach(getallheaders() as $k => $v) $headers[strtolower($k)] = $v;

            $authorization = trim(substr($headers['authorization'] ?? '', 7));
            $decoded = JWT::decode($authorization, new Key($_ENV['JWT_SECRET'], $_ENV['JWT_ALGO']));

            if ($decoded->iss === "GVP") {
                $user = User::byTokenId($request, $decoded->uid, $decoded->tkn);
            }
        } catch (ExpiredException $ex) {
            return \App\Libs\SlimEx::sendError(
                410,
                "La connexion a expirée",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        } catch (\Exception $ex) {}
        
        return $handler->handle($request->withAttribute('user', $user));
    }

}
