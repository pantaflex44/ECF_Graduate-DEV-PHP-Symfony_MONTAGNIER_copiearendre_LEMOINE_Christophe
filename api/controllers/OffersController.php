<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\Offers as Offers;
use App\Libs\SlimEx as SlimEx;
use DateTime;

class OffersController
{

    /**
     * La fonction récupère une liste d'offres en fonction des filtres fournis, du numéro de page et du nombre d'offres par page, et renvoie le résultat sous forme de réponse JSON.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le corps de la réponse, les en-têtes et le code d'état.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route supplémentaires passés au point de terminaison. Dans ce cas, il est utilisé pour récupérer les valeurs `page` et `per_page` à partir de l'URL.
     * 
     * @return Response un objet Réponse.
     */
    public function list(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $is_admin_or_worker = $user?->role === 'admin' || $user?->role === 'worker';

        try {
            $page = intval($args['page'] ?? '1');
            $per_page = intval($args['per_page'] ?? '20');

            $filters = $request->getParsedBody() ?? [];
            $offers =  Offers::list($request, !$is_admin_or_worker, $filters, $page, $per_page);

            $response->getBody()->write(json_encode($offers));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            //var_dump($ex);
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la demande.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * La fonction "filters" prend un objet de requête, récupère les filtres de la classe Offers en fonction de la requête, encode les filtres au format JSON et renvoie un objet de réponse avec les données JSON et un code d'état 200. Si une exception se produit, il renvoie une réponse d'erreur avec un code d'état 400 et un message de débogage facultatif.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps de la demande.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le corps de la réponse, les en-têtes et le code d'état.
     * 
     * @return Response un objet Réponse.
     */
    public function filters_limits(Request $request, Response $response): Response
    {
        try {
            $limits = Offers::filters_limits($request);
            $response->getBody()->write(json_encode($limits));
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
     * La fonction récupère un fichier image en fonction de l'ID et du nom de fichier fournis, et le renvoie sous forme de réponse avec le type de contenu approprié.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode de requête, les en-têtes et le corps.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le corps de la réponse, les en-têtes et le code d'état.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route supplémentaires passés à la fonction. Dans ce cas, il est utilisé pour récupérer les valeurs `id` et `file` à partir du chemin de l'URL.
     * 
     * @return Response un objet Réponse.
     */
    public function get_image(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'] ?? '';
            $file = $args['file'] ?? '';
            $w = $args['w'] ?? null;
            $h = $args['h'] ?? null;

            $size = [];
            if (!is_null($w) && is_null($h)) {
                $size['mode'] = 'scale';
                $size['percent'] = intval($w);
            } elseif (!is_null($w) && !is_null($h)) {
                $size['mode'] = 'resize';
                $size['width'] = intval($w);
                $size['height'] = intval($h);
            }

            $image = Offers::get_image($request, $id, $file, $size);

            $response->getBody()->write($image['data']);
            return $response->withHeader('Content-Type', $image['content-type'])->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la demande.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * La fonction ajoute une nouvelle offre à un système, en effectuant diverses validations sur les données d'entrée.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes, les paramètres de requête et le corps de la requête.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état et les en-têtes de réponse, ainsi que pour envoyer le corps de la réponse.
     * 
     * @return Response un objet Réponse.
     */
    public function add(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin' && $user?->role !== 'worker') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $name = ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents(trim($data['name'] ?? '')))));
            if (!SlimEx::name_validator($name)) {
                return \App\Libs\SlimEx::send_error(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'name']);
            }

            $description = \App\Libs\SlimEx::strip_accents(trim($data['description'] ?? ''));
            if (!SlimEx::description_validator($description)) {
                return \App\Libs\SlimEx::send_error(400, "Description incorrecte. Minimum 3 caractères.", ['field' => 'description']);
            }

            $price = floatval(trim($data['price'] ?? '0.00'));
            if (!SlimEx::amount_validator($price)) {
                return \App\Libs\SlimEx::send_error(400, "Montant incorrect.", ['field' => 'price']);
            }

            $release_date = trim($data['release_date'] ?? '1970-01-01');
            if (!SlimEx::release_date_validator($release_date)) {
                return \App\Libs\SlimEx::send_error(400, "Date de mise en service incorrecte.", ['field' => 'release_date']);
            }

            $mileage = intval(trim($data['mileage'] ?? '0'));
            if ($mileage <= 0) {
                return \App\Libs\SlimEx::send_error(400, "Kilométrage incorrect.", ['field' => 'mileage']);
            }

            $gallery = [];
            foreach(($uploadedFiles['gallery'] ?? []) as $file) {
                $uploadedFile = \App\Libs\SlimEx::image_validator($file);
                if ($uploadedFile['success'] === true) {
                    $gallery[] = $file;
                }
            }

            $informations = ['din' => 0, 'fuel' => '', 'type' => '', 'brand' => '', 'color' => '', 'doors' => 0, 'model' => '', 'sites' => 0, 'gearbox' => '', 'fiscal' => 0];
            foreach(array_keys($informations) as $key) {
                if (array_key_exists("informations_$key", $data)) {
                    try {
                        $value = trim($data["informations_$key"]);
                        if (gettype($informations[$key]) === 'integer') $value = intval($value);
                        if (gettype($informations[$key]) === 'double') $value = floatval($value);
                        if (gettype($informations[$key]) === 'string') $value = ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents($value))));
                        $informations[$key] = $value;
                    } catch (\Exception $ex) {}
                }
            }

            $equipments_list = array_map(function ($el) { return ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents(trim($el))))); }, $data['equipments_list'] ?? []);

            if (!Offers::add($request, $name, $description, $price, $release_date, $mileage, $gallery, $informations, $equipments_list)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible d'enregistrer cette nouvelle annonce.");
            }

            return $response->withStatus(201);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire d'enregistrement d'une nouvelle annonce'.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * La fonction met à jour une offre avec les données fournies et renvoie une réponse.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes, les paramètres de requête et le corps de la requête.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état et les en-têtes de réponse, ainsi que pour envoyer le corps de la réponse.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route supplémentaires passés à la fonction de mise à jour. Ces paramètres sont généralement utilisés pour identifier la ressource spécifique qui doit être mise à jour. Dans ce cas, le tableau `` devrait contenir une clé `'id'`, qui représente l'ID
     * 
     * @return Response un objet Réponse.
     */
    public function update(Request $request, Response$response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if ($user?->role !== 'admin' && $user?->role !== 'worker') {
            return \App\Libs\SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }

        try {
            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $id = intval($args['id']);

            $name = ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents(trim($data['name'] ?? '')))));
            if (!SlimEx::name_validator($name)) {
                return \App\Libs\SlimEx::send_error(400, "Dénomination incorrecte. Minimum 3 caractères.", ['field' => 'name']);
            }

            $description = \App\Libs\SlimEx::strip_accents(trim($data['description'] ?? ''));
            if (!SlimEx::description_validator($description)) {
                return \App\Libs\SlimEx::send_error(400, "Description incorrecte. Minimum 3 caractères.", ['field' => 'description']);
            }

            $price = floatval(trim($data['price'] ?? '0.00'));
            if (!SlimEx::amount_validator($price)) {
                return \App\Libs\SlimEx::send_error(400, "Montant incorrect.", ['field' => 'price']);
            }

            $release_date = trim($data['release_date'] ?? '1970-01-01');
            if (!SlimEx::release_date_validator($release_date)) {
                return \App\Libs\SlimEx::send_error(400, "Date de mise en service incorrecte.", ['field' => 'release_date']);
            }

            $mileage = intval(trim($data['mileage'] ?? '0'));
            if ($mileage <= 0) {
                return \App\Libs\SlimEx::send_error(400, "Kilométrage incorrect.", ['field' => 'mileage']);
            }

            $gallery = [];
            foreach (($uploadedFiles['gallery'] ?? []) as $file) {
                $uploadedFile = \App\Libs\SlimEx::image_validator($file);
                if ($uploadedFile['success'] === true) {
                    $gallery[] = $file;
                }
            }

            $informations = ['din' => 0, 'fuel' => '', 'type' => '', 'brand' => '', 'color' => '', 'doors' => 0, 'model' => '', 'sites' => 0, 'gearbox' => '', 'fiscal' => 0];
            foreach (array_keys($informations) as $key) {
                if (array_key_exists("informations_$key", $data)) {
                    try {
                        $value = trim($data["informations_$key"]);
                        if (gettype($informations[$key]) === 'integer') $value = intval($value);
                        if (gettype($informations[$key]) === 'double') $value = floatval($value);
                        if (gettype($informations[$key]) === 'string') $value = ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents($value))));
                        $informations[$key] = $value;
                    } catch (\Exception $ex) {
                    }
                }
            }

            $equipments_list = array_map(function ($el) {
                return ucwords(strtolower(\App\Libs\SlimEx::alpha_numeric_only(\App\Libs\SlimEx::strip_accents(trim($el)))));
            }, $data['equipments_list'] ?? []);

            if (!Offers::update($request, $id, $name, $description, $price, $release_date, $mileage, $gallery, $informations, $equipments_list)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier cette annonce.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification de cette annonce'.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction PHP supprime une offre si l'utilisateur dispose des autorisations nécessaires et renvoie un message d'erreur si la suppression échoue.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état et les en-têtes de réponse, ainsi que pour envoyer le corps de la réponse.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route passés à la fonction de suppression. Dans ce cas, il semble contenir un paramètre 'id', qui sert à identifier l'offre à supprimer.
     * 
     * @return Response un objet Réponse.
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

            if (!Offers::delete($request, $id)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de supprimer cette annonce.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter la suppression de cette annonce.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

    /**
     * Cette fonction PHP active une offre basée sur le rôle de l'utilisateur et met à jour son état.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param Response response Le paramètre `` est une instance de la classe `Response`, qui représente la réponse HTTP qui sera renvoyée au client. Il est utilisé pour définir le code d'état et les en-têtes de réponse, ainsi que pour envoyer le corps de la réponse.
     * @param array args Le paramètre `` est un tableau qui contient tous les paramètres de route supplémentaires passés à la fonction. Dans ce cas, il semble contenir une clé `'id'`, qui sert à récupérer l'ID de l'offre à activer.
     * 
     * @return Response un objet Réponse.
     */
    public function activate(Request $request, Response $response, array $args): Response
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

            if (!Offers::activate($request, $id, $state)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible de modifier l'état de cette annonce.");
            }

            return $response->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire de modification de l'état de cette annonce.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }
}
