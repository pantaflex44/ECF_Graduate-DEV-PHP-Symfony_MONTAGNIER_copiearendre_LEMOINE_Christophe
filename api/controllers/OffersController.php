<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\Offers as Offers;
use App\Libs\SlimEx as SlimEx;

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
            $image = Offers::get_image($request, $id, $file);

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
}
