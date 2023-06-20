<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\OpeningHours;
use App\Libs\SlimEx as SlimEx;


/* La classe OpeningHoursController gère les demandes liées aux heures d'ouverture, y compris la liste et l'ajout de nouvelles périodes d'ouverture. */

class OpeningHoursController
{

    /**
     * Cette fonction répertorie les heures d'ouverture au format JSON et renvoie une réponse avec un code d'état 200, ou envoie une réponse d'erreur avec un code d'état 400 si une exception se produit.
     * 
     * @param Request request  est une instance de la classe Request, qui représente une requête HTTP reçue par le serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps. Il est utilisé dans cette fonction pour transmettre des informations sur la requête à la méthode OpeningHours::list.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client. L'objet Response contient le corps de la réponse, les en-têtes et le code d'état. Dans cet extrait de code, le corps de la réponse est rempli avec des données encodées JSON et le Content-Type
     * 
     * @return Response Cette fonction renvoie un objet de réponse avec une liste encodée JSON des heures d'ouverture et un code d'état de 200 si la demande aboutit. Si une exception est interceptée, il renvoie une réponse d'erreur avec un code d'état de 400 et un message d'erreur. Si l'attribut `debug` est défini sur `true` dans la requête, il inclut également un message de débogage avec les détails de l'erreur.
     */
    public function list(Request $request, Response $response): Response
    {
        try {
            $response->getBody()->write(json_encode(OpeningHours::list($request)));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter la demande.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction PHP ajoute une nouvelle période d'heures d'ouverture à un planning, avec validation des entrées et gestion des erreurs.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui est utilisée pour représenter une réponse HTTP qui sera renvoyée au client. Il contient des informations telles que le code d'état, les en-têtes et le corps de la réponse.
     * 
     * @return Response Cette fonction renvoie un objet de réponse.
     */
    public function add(Request $request, Response $response): Response
    {
        \App\Libs\SlimEx::onlyAdmin($request);

        try {
            $data = $request->getParsedBody();

            $dayOfWeek = intval($data['dayOfWeek']);
            if ($dayOfWeek < 0 || $dayOfWeek > 6) {
                return \App\Libs\SlimEx::sendError(400, "Jour d'ouverture incorrect.", ['field' => 'dayOfWeek']);
            }

            $open_hours = intval(trim($data['open_hours']));
            if ($open_hours < 0 || $open_hours > 24) {
                return \App\Libs\SlimEx::sendError(400, "Heure d'ouverture incorrecte.", ['field' => 'open_hours']);
            }
            if ($open_hours === 24) $open_hours = 0;

            $open_minutes = intval(trim($data['open_minutes']));
            if ($open_minutes < 0 || $open_minutes > 60) {
                return \App\Libs\SlimEx::sendError(400, "Minutes d'ouverture incorrecte.", ['field' => 'open_minutes']);
            }
            if ($open_minutes === 60) $open_minutes = 0;

            $close_hours = intval(trim($data['close_hours']));
            if ($close_hours < 0 || $close_hours > 24) {
                return \App\Libs\SlimEx::sendError(400, "Heure de fermeture incorrecte.", ['field' => 'close_hours']);
            }
            if ($close_hours === 24) $close_hours = 0;

            $close_minutes = intval(trim($data['close_minutes']));
            if ($close_minutes < 0 || $close_minutes > 60) {
                return \App\Libs\SlimEx::sendError(400, "Minutes de fermeture incorrecte.", ['field' => 'close_minutes']);
            }
            if ($close_minutes === 60) $close_minutes = 0;

            if (OpeningHours::start_exists($request, $dayOfWeek, $open_hours, $open_minutes)) {
                return \App\Libs\SlimEx::sendError(400, "Ouverture dans une période connue.", ['field' => 'open_hours']);
            }
            if (OpeningHours::end_exists($request, $dayOfWeek, $close_hours, $close_minutes)) {
                return \App\Libs\SlimEx::sendError(400, "Fermeture dans une période connue.", ['field' => 'open_hours']);
            }

            if (!OpeningHours::add($request, $dayOfWeek, $open_hours, $open_minutes, $close_hours, $close_minutes)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible d'enregistrer cette nouvelle période d'ouverture.");
            }

            return $response->withStatus(201);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter le formulaire d'enregistrement d'une nouvelle période d'ouverture.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction met à jour les heures d'ouverture d'un commerce en fonction des données fournies dans la demande.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP adressée au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui représente une réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état, les en-têtes et le corps de la réponse.
     * @param array args  est un tableau de paramètres passés dans la route d'URL. Il peut contenir des segments dynamiques de l'URL, qui sont définis dans la définition de route. Ces paramètres sont accessibles dans la méthode du contrôleur à l'aide du tableau .
     * 
     * @return Response Cette fonction renvoie un objet Response.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        \App\Libs\SlimEx::onlyAdmin($request);

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            $dayOfWeek = intval($data['dayOfWeek']);
            if ($dayOfWeek < 0 || $dayOfWeek > 6) {
                return \App\Libs\SlimEx::sendError(400, "Jour d'ouverture incorrect.", ['field' => 'dayOfWeek']);
            }

            $open_hours = intval(trim($data['open_hours']));
            if ($open_hours < 0 || $open_hours > 24) {
                return \App\Libs\SlimEx::sendError(400, "Heure d'ouverture incorrecte.", ['field' => 'open_hours']);
            }
            if ($open_hours === 24) $open_hours = 0;

            $open_minutes = intval(trim($data['open_minutes']));
            if ($open_minutes < 0 || $open_minutes > 60) {
                return \App\Libs\SlimEx::sendError(400, "Minutes d'ouverture incorrecte.", ['field' => 'open_minutes']);
            }
            if ($open_minutes === 60) $open_minutes = 0;

            $close_hours = intval(trim($data['close_hours']));
            if ($close_hours < 0 || $close_hours > 24) {
                return \App\Libs\SlimEx::sendError(400, "Heure de fermeture incorrecte.", ['field' => 'close_hours']);
            }
            if ($close_hours === 24) $close_hours = 0;

            $close_minutes = intval(trim($data['close_minutes']));
            if ($close_minutes < 0 || $close_minutes > 60) {
                return \App\Libs\SlimEx::sendError(400, "Minutes de fermeture incorrecte.", ['field' => 'close_minutes']);
            }
            if ($close_minutes === 60) $close_minutes = 0;

            if (!OpeningHours::update($request, $id, $dayOfWeek, $open_hours, $open_minutes, $close_hours, $close_minutes)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible de modifier cette période d'ouverture.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter le formulaire de modification de la période d'ouverture.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui supprime un enregistrement d'heure d'ouverture et renvoie un message d'erreur si la suppression échoue.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui est utilisée pour représenter une réponse HTTP qui sera renvoyée au client. Il contient des informations telles que le code d'état de la réponse, les en-têtes et le corps. Dans cette fonction, il est utilisé pour renvoyer une réponse avec un code d'état de 200 si
     * @param array args  est un tableau de paramètres de route transmis à la méthode du contrôleur. Dans ce cas, il est prévu qu'il contienne un paramètre 'id' qui est utilisé pour identifier l'enregistrement des heures d'ouverture à supprimer.
     * 
     * @return Response Cette fonction renvoie un objet Response. Le code d'état de la réponse est 200 si la suppression a réussi et 400 en cas d'erreur. S'il y a une exception, il renvoie une erreur 400 avec un message d'erreur et, si l'indicateur de débogage est défini, un tableau avec un message de débogage.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        \App\Libs\SlimEx::onlyAdmin($request);

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            if (!OpeningHours::delete($request, $id)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible de supprimer cette plage horaire.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter la suppression de la plage horaire.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }
}
