<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use App\Libs\User as User;
use App\Libs\SlimEx as SlimEx;

/* La classe UsersController contient des méthodes pour ajouter, mettre à jour, supprimer, activer et vérifier l'existence d'utilisateurs, avec un accès limité aux utilisateurs administrateurs. */
class UsersController
{
    /**
     * Cette fonction PHP ajoute un nouvel utilisateur au système si l'utilisateur demandeur est un administrateur et que l'e-mail, le mot de passe et le nom d'affichage fournis répondent à certains critères de validation.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client après le traitement de la requête. Il contient des informations telles que le code d'état, les en-têtes et le corps de la réponse.
     *
     * @return Response Cette fonction renvoie un objet de réponse. Si l'utilisateur n'est pas un administrateur, il renvoie une réponse d'erreur 403. Si les données fournies dans la demande ne sont pas valides, elle renvoie une réponse d'erreur 400 avec un message d'erreur et un nom de champ spécifiques. Si l'utilisateur est ajouté avec succès, il renvoie une réponse de réussite 200. S'il y a une exception levée pendant le processus, il renvoie une erreur 400
     */
    public function add(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $email = trim($data['email']);
            if (!SlimEx::email_validator($email)) {
                return \App\Libs\SlimEx::send_error(400, "Adresse email incorrecte.", ['field' => 'email']);
            }
            if (User::exists($request, $email)) {
                return \App\Libs\SlimEx::send_error(400, "Adresse email déjà utilisée.", ['field' => 'email']);
            }

            $password = trim($data['password']);
            if (!SlimEx::password_validator($password)) {
                return \App\Libs\SlimEx::send_error(400, "Mot de passe incorrect. Minimum 8 caractères, 1 minuscule, 1 majuscule et 1 caractère spécial.", ['field' => 'password']);
            }

            $display_name = trim($data['display_name']);
            if (!SlimEx::display_name_validator($display_name)) {
                return \App\Libs\SlimEx::send_error(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'display_name']);
            }

            if (!User::add($request, $email, $password, $display_name)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible d'enregistrer ce nouvel utilisateur.");
            }

            return $response->withStatus(201);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire d'enregistrement du nouvel utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction met à jour les informations de l'utilisateur si l'utilisateur est un administrateur et que les données fournies sont valides.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response du framework Slim. Il est utilisé pour renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres passés dans la route d'URL. Par exemple, si l'URL est "/users/5", alors  serait ['id' => 5].
     *
     * @return Response Cette fonction renvoie un objet de réponse avec un code d'état de 200 si l'utilisateur est un administrateur et que l'opération de mise à jour a réussi. S'il y a une erreur, il renvoie un objet de réponse d'erreur avec un code d'état de 400 et un message d'erreur.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $email = trim($data['email']);
            if (!SlimEx::email_validator($email)) {
                return \App\Libs\SlimEx::send_error(400, "Adresse email incorrecte.", ['field' => 'email']);
            }
            if (!User::exists($request, $email)) {
                return \App\Libs\SlimEx::send_error(400, "Adresse email inconnue.", ['field' => 'email']);
            }

            $display_name = trim($data['display_name']);
            if (!SlimEx::display_name_validator($display_name)) {
                return \App\Libs\SlimEx::send_error(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'display_name']);
            }

            if (!User::update($request, $id, $email, $display_name)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier cet utilisateur.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification de l'utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction PHP supprime un utilisateur si le demandeur est un administrateur.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP adressée au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response du framework Slim. Il est utilisé pour renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres de route transmis à la méthode du contrôleur. Dans ce cas, il est censé contenir un paramètre 'id' qui est utilisé pour identifier l'utilisateur à supprimer.
     *
     * @return Response Cette fonction renvoie un objet de réponse avec un code d'état de 200 si l'utilisateur est supprimé avec succès, ou un objet de réponse d'erreur avec un code d'état de 400 et un message d'erreur si la suppression échoue. Si une exception est interceptée pendant le processus de suppression, il renvoie également un objet de réponse d'erreur avec un code d'état de 400 et un message d'erreur, ainsi qu'un message de débogage facultatif si le 'debug
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            if (!User::delete($request, $id)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de supprimer cet utilisateur.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la suppression de l'utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction active ou désactive un compte utilisateur en fonction de l'état de la saisie, mais uniquement si l'utilisateur à l'origine de la demande est un administrateur.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP adressée au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il est utilisé pour renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres passés dans la route d'URL. Par exemple, si l'URL est "/users/activate/5", alors  serait ['id' => 5].
     *
     * @return Response Cette fonction renvoie un objet de réponse avec un code d'état de 200 si l'utilisateur est activé avec succès, ou une réponse d'erreur avec un code d'état de 400 s'il y a une erreur dans le processus d'activation.
     */
    public function activate(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $state = intval(trim($data['state']));
            if ($state !== 1) {
                $state = 0;
            }

            if (!User::activate($request, $id, $state)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier l'état de cet utilisateur.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification de l'état de cet utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction vérifie si un utilisateur avec un e-mail donné existe et renvoie une réponse JSON indiquant si l'utilisateur existe ou non.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il est utilisé pour construire et renvoyer une réponse HTTP au client. L'objet de réponse contient le corps de la réponse, les en-têtes et le code d'état. Dans cette fonction, l'objet de réponse est utilisé pour renvoyer une réponse encodée en JSON au
     *
     * @return Response Cette fonction renvoie une réponse JSON indiquant si un utilisateur avec un email donné existe ou non. Si l'utilisateur qui fait la demande n'est pas un administrateur, une réponse d'erreur 403 est renvoyée. S'il y a une erreur lors du traitement de la demande, une réponse d'erreur 400 est renvoyée.
     */
    public function exists(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $email = trim($data['email']);
            $exists = User::exists($request, $email);

            $response->getBody()->write(json_encode([
                'email'     => $email,
                'exists'    => $exists
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la demande.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui répertorie les utilisateurs et renvoie une réponse JSON, avec gestion des erreurs.
     *
     * @param Request request  est une instance de la classe Request du framework Slim. Il représente une requête HTTP et contient des informations telles que la méthode de requête, les en-têtes et les paramètres.
     * @param Response response  est une instance de la classe Response du framework Slim. Il est utilisé pour construire et renvoyer une réponse HTTP au client.
     *
     * @return Response Cette fonction renvoie un objet de réponse avec une liste d'utilisateurs encodée en JSON et un code d'état de 200 si la demande aboutit. Si une exception est interceptée, il renvoie un objet de réponse d'erreur avec un code d'état de 400 et un message d'erreur. Si l'attribut `debug` est défini sur `true` dans la requête, il inclut également le message d'exception dans la réponse.
     */
    public function list(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $response->getBody()->write(json_encode(User::list($request)));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la demande.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction traite la demande de changement de mot de passe d'un utilisateur et valide le nouveau mot de passe avant de le mettre à jour.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il est utilisé pour renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres passés dans la route d'URL. Par exemple, si l'URL est "/users/123/password", alors ['id'] serait égal à 123.
     *
     * @return Response Cette fonction renvoie un objet de réponse avec un code d'état de 200 si la mise à jour du mot de passe a réussi, ou un objet de réponse avec un code d'état de 400 et un message d'erreur en cas d'erreur de mise à jour du mot de passe.
     */
    public function password(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $old_password = trim($data['old_password']);

            $new_password = trim($data['new_password']);
            if (!SlimEx::password_validator($new_password)) {
                return \App\Libs\SlimEx::send_error(400, "Mot de passe incorrect. Minimum 8 caractères, 1 minuscule, 1 majuscule et 1 caractère spécial.", ['field' => 'new_password']);
            }

            if (!User::password($request, $id, $old_password, $new_password)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier le mot de passe de cet utilisateur.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification du mot de passe de cet utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui réinitialise le mot de passe d'un utilisateur et renvoie le nouveau mot de passe.
     *
     * @param Request request Le paramètre  est une instance de la classe Request dans le framework Slim, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il est utilisé pour construire et renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres passés dans la route d'URL. Par exemple, si l'URL est "/users/1/reset", alors ['id'] serait égal à 1.
     *
     * @return Response Cette fonction renvoie un objet de réponse avec un corps encodé en JSON contenant le nouveau mot de passe généré pour l'utilisateur dont l'ID est fourni dans le paramètre URL. Si une erreur se produit, il renvoie un objet de réponse avec un message d'erreur et un code d'état de 400.
     */
    public function reset(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $ret = User::reset($request, $id);
            $result = $ret[0];
            $new_password = $ret[1];
            if (!$result) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier le mot de passe de cet utilisateur.");
            }

            $response->getBody()->write(json_encode([
                'new_password'       => $new_password
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification du mot de passe de cet utilisateur.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }


}
