<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\Comments as Comments;
use App\Libs\SlimEx as SlimEx;
use DateTime;

/* Il s'agit d'une classe PHP pour gérer les commentaires, avec des méthodes pour répertorier tous les commentaires et répertorier uniquement les commentaires approuvés. */

class CommentsController
{

    /**
     * Cette fonction PHP répertorie les commentaires et renvoie une réponse JSON.
     * 
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP reçue par le serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps. Il est utilisé dans cette fonction pour transmettre la requête à la méthode Comments::list, qui utilisera les informations dans
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client. L'objet Response contient le corps de la réponse, les en-têtes et le code d'état.
     * 
     * @return Response Cette fonction renvoie un objet de réponse avec une liste de commentaires encodée JSON et un code d'état de 200 en cas de succès, ou une réponse d'erreur avec un code d'état de 400 si une exception est interceptée.
     */
    public function list(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin' && $user?->role !== 'worker') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $response->getBody()->write(json_encode(Comments::list($request)));
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
     * Cette fonction renvoie une réponse JSON de commentaires approuvés en fonction des paramètres de la requête.
     * 
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP reçue par le serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps. Dans cette fonction, le paramètre  est utilisé pour transmettre des données à la méthode Comments::approved_list().
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client. Il contient le corps de la réponse, les en-têtes et le code d'état.
     * 
     * @return Response Cette fonction renvoie un objet de réponse avec une liste encodée JSON de commentaires approuvés et un code d'état de 200 si la demande aboutit. S'il y a une exception, il renverra une réponse d'erreur avec un code d'état de 400 et un message d'erreur.
     */
    public function approved_list(Request $request, Response $response): Response
    {
        try {
            $response->getBody()->write(json_encode(Comments::approved_list($request)));
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
     * Cette fonction ajoute un nouveau commentaire à une base de données avec des contrôles de validation pour les champs de nom, de commentaire et d'évaluation.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui est utilisée pour représenter une réponse HTTP qui sera renvoyée au client. Il contient des informations telles que le code d'état de la réponse, les en-têtes et le corps. Dans cette fonction spécifique, il est utilisé pour renvoyer une réponse avec un code d'état de 201
     * 
     * @return Response Cette fonction renvoie un objet de réponse HTTP PSR-7.
     */
    public function add(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            $name = trim($data['name']);
            if (!SlimEx::name_validator($name)) {
                return \App\Libs\SlimEx::send_error(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'name']);
            }

            $comment = trim($data['comment']);
            /*if (!SlimEx::description_validator($comment)) {
                return \App\Libs\SlimEx::send_error(400, "Commentaire manquant.", ['field' => 'comment']);
            }*/

            $rating = floatval(trim($data['rating']));
            if ($rating > 0) $rating = round($rating * 2) / 2;
            if ($rating < 0.0 || $rating > 5.0) {
                return \App\Libs\SlimEx::send_error(400, "Note incorrecte.", ['field' => 'rating']);
            }

            $ip = \App\Libs\SlimEx::get_user_ip_addr();
            $last_posted = Comments::last_posted_interval($request, $ip);
            if (!is_null($last_posted)) {
                $now = new \DateTimeImmutable();
                $last = $now->add($last_posted);
                $allowed = $now->add(new \DateInterval('PT30M'));
                if ($last < $allowed) {
                    return \App\Libs\SlimEx::send_error(400, "Vous venez de poster un avis. Veuillez patienter 30 minutes entre chaque envoie.", ['field' => 'comment']);
                }
            }

            if (!Comments::add($request, $name, $comment, $rating, $ip)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible d'enregistrer ce nouvel avis.");
            }

            return $response->withStatus(201);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire d'enregistrement d'un nouvel avis'.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui supprime un commentaire et renvoie un message d'erreur si la suppression n'est pas possible.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request du framework Slim, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui est utilisée pour représenter une réponse HTTP qui sera renvoyée au client. Il contient des informations telles que le code d'état de la réponse, les en-têtes et le corps. Dans cette fonction, il est utilisé pour renvoyer une réponse avec un code d'état de 200 si
     * @param array args  est un tableau de paramètres de route transmis à la méthode du contrôleur. Dans ce cas, il est censé contenir un paramètre 'id' qui est utilisé pour identifier le commentaire à supprimer.
     * 
     * @return Response Cette fonction renvoie un objet Response.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin' && $user?->role !== 'worker') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            if (!Comments::delete($request, $id)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de supprimer cet avis.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la suppression de l'avis.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * La fonction "approuver" vérifie le rôle de l'utilisateur et met à jour l'état d'un commentaire en fonction des données fournies.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande, telles que la méthode de la demande, les en-têtes et le corps.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route supplémentaires passés à la fonction. Dans ce cas, il semble contenir une clé `'id'`, qui est utilisée pour récupérer l'ID du commentaire.
     * 
     * @return Response un objet Réponse.
     */
    public function approve(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin' && $user?->role !== 'worker') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $state = intval(trim($data['state']));
            if ($state !== 1) {
                $state = 0;
            }

            if (!Comments::approve($request, $id, $state)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier l'état de ce commentaire.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification de l'état de ce commentaire.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }
}
