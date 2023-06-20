<?php

namespace App\Libs;

use Firebase\JWT\JWT;

/**
 * La fonction crée un jeton Web JSON (JWT) avec des paramètres spécifiés et le renvoie avec son heure d'expiration.
 *
 * @param user  est une variable qui représente un objet utilisateur. Il est probable que cette fonction soit utilisée pour créer un jeton Web JSON (JWT) pour un utilisateur spécifique.
 * @param user_token  est un jeton généré pour un utilisateur spécifique. Il est inclus dans le jeton JWT en tant que revendication 'tkn' (jeton). Ce jeton peut être utilisé pour authentifier l'utilisateur dans les demandes ultérieures.
 *
 * @return array Un tableau avec deux clés : 'jwt' et 'exp'. La clé 'jwt' contient un jeton Web JSON (JWT) qui a été encodé à l'aide de la bibliothèque JWT, et la clé 'exp' contient l'heure d'expiration du JWT.
 */
function createJwtToken($user, $user_token): array
{
    $iat = time();
    $exp = $iat + intval($_ENV['JWT_LIVE']);
    $token = [
        'iss' => "GVP",
        'iat' => $iat,
        'exp' => $exp,
        'tkn' => $user_token,
        'uid' => $user->id
    ];

    $jwt = JWT::encode($token, $_ENV['JWT_SECRET'], $_ENV['JWT_ALGO']);
    return ['jwt' => $jwt, 'exp' => $exp];
}

/**
 * La fonction crée un jeton unique d'une longueur spécifiée en utilisant random_bytes ou openssl_random_pseudo_bytes.
 *
 * @param length Le paramètre  est une valeur entière facultative qui spécifie la longueur du jeton unique à générer. Si aucune valeur n'est fournie, la longueur par défaut de 20 caractères sera utilisée.
 *
 * @return string Cette fonction renvoie une chaîne de jeton unique de longueur spécifiée (la longueur par défaut est 20). Le jeton est généré à l'aide de la fonction `random_bytes` ou `openssl_random_pseudo_bytes`, selon celle qui est disponible. Si aucune fonction n'est disponible, une chaîne vide est renvoyée.
 */
function createUniqToken($length = 20): string
{
    if (function_exists('random_bytes')) {
        $bytes = random_bytes(ceil($length / 2));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
    } else {
        return "";
    }
    return substr(bin2hex($bytes), 0, $length);
}
