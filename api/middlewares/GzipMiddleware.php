<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;


/* La classe GzipMiddleware est chargée de compresser la réponse à l'aide de l'encodage gzip si la requête le prend en charge. */
class GzipMiddleware
{
    /**
     * La fonction vérifie si la requête prend en charge la compression gzip et, si c'est le cas, compresse la réponse à l'aide de l'encodage gzip.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * @param RequestHandler handler Le paramètre `` est une instance de l'interface `RequestHandler`. Il représente le middleware suivant ou le gestionnaire d'application final qui sera appelé dans la pile middleware. Il est chargé de traiter la demande entrante et de renvoyer une réponse.
     * 
     * @return Response un objet Réponse.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (
            $request->hasHeader('Accept-Encoding') &&
            stristr($request->getHeaderLine('Accept-Encoding'), 'gzip') === false
        ) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        if ($response->hasHeader('Content-Encoding')) {
            return $handler->handle($request);
        }

        $deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
        $compressed = deflate_add($deflateContext, (string)$response->getBody(), \ZLIB_FINISH);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $compressed);
        rewind($stream);

        return $response
            ->withHeader('Content-Encoding', 'gzip')
            ->withHeader('Content-Length', strlen($compressed))
            ->withBody(new \Slim\Psr7\Stream($stream));
    }
}
