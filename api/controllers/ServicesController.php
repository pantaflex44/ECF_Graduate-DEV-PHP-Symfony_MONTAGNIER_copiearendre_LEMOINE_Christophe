<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\Service as Service;
use App\Libs\SlimEx as SlimEx;

/* La classe ServicesController contient des méthodes pour répertorier et ajouter des services, avec gestion des erreurs et validation. */

class ServicesController
{
    /**
     * Cette fonction répertorie les éléments et renvoie une réponse JSON avec un code d'état 200 ou un message d'erreur avec un code d'état 400.
     * 
     * @param Request request  est une instance de la classe Request, qui contient des informations sur la requête HTTP adressée au serveur, telles que la méthode de requête, les en-têtes et le corps. Il est utilisé pour récupérer les données envoyées par le client et pour transmettre les données au code côté serveur pour traitement.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client. L'objet Response contient le corps de la réponse, les en-têtes et le code d'état. Dans cet extrait de code, le corps de la réponse est rempli avec le résultat encodé en JSON d'un service
     * 
     * @return Response Cette fonction renvoie un objet de réponse avec une liste de services encodée en JSON et un code d'état de 200 si la demande aboutit. Si une exception est interceptée, il renvoie une réponse d'erreur avec un code d'état de 400 et un message d'erreur. Si l'attribut 'debug' est défini sur true dans la requête, il inclut également un message de débogage avec la réponse d'erreur.
     */
    public function list(Request $request, Response $response): Response
    {
        try {
            $response->getBody()->write(json_encode(Service::list($request)));
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
     * Cette fonction PHP ajoute un nouveau service à un système, validant les données d'entrée et téléchargeant une image.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response dans le framework Slim. Il représente la réponse HTTP qui sera renvoyée au client après le traitement de la requête. La méthode add() est censée renvoyer une instance de Response.
     * 
     * @return Response un objet Réponse.
     */
    public function add(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::sendError(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $name = trim($data['name']);
            if (!SlimEx::nameValidator($name)) {
                return \App\Libs\SlimEx::sendError(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'name']);
            }

            $amount = floatval(trim($data['amount']));
            if (!SlimEx::amountValidator($amount)) {
                return \App\Libs\SlimEx::sendError(400, "Montant incorrect.", ['field' => 'amount']);
            }

            $description = trim($data['description']);
            if (!SlimEx::descriptionValidator($description)) {
                return \App\Libs\SlimEx::sendError(400, "Description incorrecte. Minimum 3 caractères.", ['field' => 'description']);
            }

            $uploadedFile = \App\Libs\SlimEx::imageValidator($uploadedFiles['image']);
            if ($uploadedFile['success'] !== true) {
                return \App\Libs\SlimEx::sendError(400, $uploadedFile['data'], ['field' => 'image']);
            }
            $dataUri = $uploadedFile['data'];

            if (!Service::add($request, $name, $amount, $description, $dataUri)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible d'enregistrer ce nouveau service.");
            }

            return $response->withStatus(201);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter le formulaire d'enregistrement d'un nouveau service'.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui met à jour un service avec de nouvelles informations et une image.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP reçue par l'application. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response, qui représente une réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état, les en-têtes et le corps de la réponse.
     * @param array args  est un tableau de paramètres de route transmis à la méthode du contrôleur. Ces paramètres sont extraits du chemin de l'URL par le framework Slim et transmis à la méthode du contrôleur en tant qu'argument. Dans ce cas, la variable  est utilisée pour extraire le paramètre 'id' du chemin de l'URL, qui est utilisé
     * 
     * @return Response un objet Réponse. Si l'utilisateur n'est pas un administrateur, il renverra une réponse d'erreur 403. S'il y a une erreur avec les données du formulaire, il renverra une réponse d'erreur 400 avec un message et le champ qui a causé l'erreur. Si la mise à jour réussit, elle renverra une réponse de réussite 200. S'il y a une exception, il retournera un
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::sendError(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $id = intval($args['id']);

            $name = trim($data['name']);
            if (!SlimEx::nameValidator($name)) {
                return \App\Libs\SlimEx::sendError(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'name']);
            }

            $amount = floatval(trim($data['amount']));
            if (!SlimEx::amountValidator($amount)) {
                return \App\Libs\SlimEx::sendError(400, "Montant incorrect.", ['field' => 'amount']);
            }

            $description = trim($data['description']);
            if (!SlimEx::descriptionValidator($description)) {
                return \App\Libs\SlimEx::sendError(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'description']);
            }

            $uploadedFile = \App\Libs\SlimEx::imageValidator($uploadedFiles['image']);
            if ($uploadedFile['success'] !== true) {
                return \App\Libs\SlimEx::sendError(400, $uploadedFile['data'], ['field' => 'image']);
            }
            $dataUri = $uploadedFile['data'];

            if (!Service::update($request, $id, $name, $amount, $description, $dataUri)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible de modifier ce service.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter le formulaire de modification du service.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Il s'agit d'une fonction PHP qui supprime un service et renvoie un message d'erreur si la suppression n'est pas possible.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request du framework Slim, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response  est une instance de la classe Response du framework Slim. Il est utilisé pour renvoyer une réponse HTTP au client.
     * @param array args  est un tableau de paramètres de route transmis à la méthode du contrôleur. Dans ce cas, il contient le paramètre 'id' qui permet d'identifier le service à supprimer.
     * 
     * @return Response Cette fonction renvoie un objet Response.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin') {
            return \App\Libs\SlimEx::sendError(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();

            $id = intval($args['id']);

            if (!Service::delete($request, $id)) {
                return \App\Libs\SlimEx::sendError(400, "Impossible de supprimer ce service.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::sendError(
                400,
                "Impossible de traiter la suppression du service.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }
}
