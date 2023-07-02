<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use App\Libs\User as User;

/* La `class AuthController` est une classe PHP qui contient des méthodes pour gérer les demandes liées à l'authentification des utilisateurs telles que la connexion, l'actualisation et la déconnexion. La méthode "login" prend un nom d'utilisateur et un mot de passe à partir d'une requête, vérifie si l'utilisateur existe, génère un jeton Web JSON (JWT) si l'utilisateur existe et renvoie une réponse avec le JWT et les informations de l'utilisateur. La méthode "refresh" actualise le JWT pour un utilisateur authentifié et renvoie une réponse avec les informations utilisateur mises à jour. La méthode `logout` déconnecte un utilisateur authentifié. */
class AuthController
{
    /**
     * Cette fonction gère la connexion de l'utilisateur, vérifie les informations d'identification, génère un jeton JWT et renvoie les données utilisateur avec le jeton.
     *
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps. Dans cette fonction, il est utilisé pour récupérer les informations d'identification de l'utilisateur (nom d'utilisateur et mot de passe) à partir du corps de la requête.
     * @param Response response  est une instance de la classe Response, qui est utilisée pour créer et renvoyer des réponses HTTP au client. Il contient le corps de la réponse, les en-têtes et le code d'état. Dans cette fonction, il est utilisé pour écrire la réponse JSON et définir les en-têtes et le code d'état appropriés avant de la renvoyer au
     *
     * @return Cette fonction renvoie une réponse JSON avec un jeton JWT, une heure d'expiration et des données utilisateur si la connexion réussit. Si les informations d'identification de l'utilisateur ne sont pas valides, il renvoie un code d'état 401 avec un message d'erreur. Si le compte utilisateur est bloqué, il renvoie un code d'état 403 avec un message d'erreur. S'il y a une erreur lors de la définition du jeton utilisateur, il renvoie un code d'état 500.
     */
    public function login(Request $request, Response $response): Response
    {
        $user = User::byCredentials($request);
        if(is_null($user)) {
            return \App\Libs\SlimEx::send_error(401, "Nom d'utilisateur ou mot de passe invalide.");
        }

        if ($user->active !== 1) {
            return \App\Libs\SlimEx::send_error(403, "Compte bloqué.");
        }

        $new_user_token = \App\Libs\createUniqToken();
        if (!$user->setToken($new_user_token)) {
            return $response->withStatus(500);
        }

        $jwtToken = \App\Libs\createJwtToken($user, $new_user_token);

        $response->getBody()->write(json_encode([
            'jwt'       => $jwtToken['jwt'],
            'exp'       => $jwtToken['exp'],
            'user'      => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * La fonction actualise le jeton utilisateur et renvoie un nouveau jeton JWT avec un délai d'expiration.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response qui représente une réponse HTTP qui sera renvoyée au client. Il contient le corps de la réponse, les en-têtes et le code d'état. Dans cette fonction, il est utilisé pour envoyer une réponse JSON contenant un nouveau jeton JWT et son heure d'expiration.
     *
     * @return Cette fonction renvoie une réponse JSON avec un nouveau jeton JWT et son heure d'expiration. La réponse a un code d'état de 200 et un type de contenu application/json.
     */
    public function refresh(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_null($user)) {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        $new_user_token = \App\Libs\createUniqToken();
        $user->setToken($new_user_token);

        $jwtToken = \App\Libs\createJwtToken($user, $new_user_token);
        $response->getBody()->write(json_encode([
            'jwt'       => $jwtToken['jwt'],
            'exp'       => $jwtToken['exp'],
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Cette fonction PHP déconnecte un utilisateur en définissant son jeton sur une chaîne vide.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP adressée au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response Le paramètre  est une instance de la classe Response, qui est utilisée pour renvoyer une réponse HTTP au client. Il contient des informations telles que le code d'état de la réponse, les en-têtes et le corps. Dans cette fonction, le paramètre  est utilisé pour renvoyer un code d'état 200 pour indiquer que
     *
     * @return une réponse avec un code d'état de 200.
     */
    public function logout(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_null($user)) {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }
        
        $logedout = $user->setToken('');

        return $response->withStatus($logedout ? 200 : 500);
    }

}
