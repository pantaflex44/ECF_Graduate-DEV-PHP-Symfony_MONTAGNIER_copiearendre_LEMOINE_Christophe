<?php

namespace App\Libs;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile as UploadedFile;
use Slim\Psr7\Response;

class SlimEx
{
    /**
     * Cette fonction envoie une réponse d'erreur au format JSON avec un code d'état HTTP spécifié, un message et un contenu facultatif.
     *
     * @param int code Code d'état HTTP à renvoyer dans la réponse. Il doit s'agir d'une valeur entière.
     * @param string message Le paramètre message est une chaîne qui représente le message d'erreur qui sera renvoyé dans la réponse. Il doit fournir une explication claire et concise de l'erreur qui s'est produite.
     * @param array content Le paramètre `` est un tableau facultatif qui peut être utilisé pour inclure des données supplémentaires dans le corps de la réponse. Il est fusionné avec le tableau ``, qui contient les clés `success` et `message`, avant d'être encodé en JSON et écrit dans le corps de la réponse. Cela permet plus
     *
     * @return Response Un objet de réponse PSR-7 avec un corps codé JSON contenant un message d'erreur et un contenu facultatif, ainsi qu'un code d'état HTTP spécifié et un en-tête Content-Type.
     */
    public static function sendError(int $code, string $message, array $content = []): Response
    {
        $response = new Response();
        $data = [
            'success'   => false,
            'message'   => $message
        ];
        $response->getBody()->write(json_encode(array_merge($data, $content)));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
    }

    /**
     * Il s'agit d'une fonction PHP qui valide un mot de passe en fonction de certains critères tels que la longueur, la présence de lettres minuscules et majuscules, de chiffres et de caractères spéciaux.
     *
     * @param string password Le paramètre d'entrée de la fonction est une variable de chaîne nommée , qui représente le mot de passe qui doit être validé.
     *
     * @return bool une valeur booléenne. Elle renverra true si la chaîne de mot de passe passée en argument correspond au modèle d'expression régulière spécifié dans la fonction utilisant la fonction preg_match_all(), et false sinon.
     */
    public static function passwordValidator(string $password): bool
    {
        return preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', trim($password));
    }

    /**
     * La fonction vérifie si un nom d'affichage donné a une longueur d'au moins 3 caractères.
     *
     * @param string display_name Le paramètre "display_name" est une chaîne qui représente le nom d'affichage d'un utilisateur. La fonction "displayNameValidator" prend cette chaîne en entrée et renvoie une valeur booléenne indiquant si le nom d'affichage est valide ou non. Dans ce cas, la fonction vérifie si la longueur du nom d'affichage coupé est
     *
     * @return bool une valeur booléenne, qui est `true` si la longueur de la chaîne `` coupée est supérieure ou égale à 3, et `false` sinon.
     */
    public static function displayNameValidator(string $display_name): bool
    {
        return strlen(trim($display_name)) >= 3;
    }

    /**
     * Cette fonction PHP vérifie si une chaîne donnée a une longueur d'au moins 3 caractères après découpage.
     * 
     * @param string name Le paramètre "name" est une chaîne qui représente le nom d'un service.
     * 
     * @return bool Une valeur booléenne indiquant si la longueur de la chaîne d'entrée coupée est supérieure ou égale à 3.
     */
    public static function nameValidator(string $name): bool
    {
        return strlen(trim($name)) >= 3;
    }

    /**
     * Cette fonction valide si une description de service donnée n'est pas vide.
     * 
     * @param string description Le paramètre "description" est une chaîne qui représente la description d'un service.
     * 
     * @return bool Une valeur booléenne est renvoyée. Il sera `true` si la longueur de la chaîne `` coupée est supérieure à 0, et `false` sinon.
     */
    public static function descriptionValidator(string $description): bool
    {
        return strlen(trim($description)) > 0;
    }

    /**
     * La fonction vérifie si une adresse e-mail donnée est valide à l'aide de la fonction intégrée filter_var de PHP.
     *
     * @param string email Le paramètre email est une variable de chaîne qui représente une adresse e-mail qui doit être validée.
     *
     * @return bool Une valeur booléenne indiquant si la chaîne d'e-mail fournie est une adresse e-mail valide ou non.
     */
    public static function emailValidator(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Cette fonction PHP valide si un montant donné est supérieur ou égal à zéro.
     * 
     * @param float amount Le paramètre « montant » est un type de données flottant représentant une valeur monétaire. La fonction "amountValidator" vérifie si le montant est supérieur ou égal à zéro, ce qui est un contrôle de validation courant pour les valeurs monétaires.
     * 
     * @return bool La fonction `amountValidator` renvoie une valeur booléenne. Il renverra `true` si le paramètre `` est supérieur ou égal à 0,00, et `false` sinon.
     */
    public static function amountValidator(float $amount): bool
    {
        return ($amount >= 0.00);
    }

    /**
     * Cette fonction valide un fichier image téléchargé en vérifiant son extension, sa taille et en le convertissant en un URI de données.
     * 
     * @param UploadedFile uploadedFile Une instance de la classe UploadedFile représentant le fichier image téléchargé.
     * 
     * @return array un tableau à deux éléments. Le premier élément est une valeur booléenne indiquant si l'image téléchargée est valide ou non. Le deuxième élément est un message de chaîne fournissant plus d'informations sur le résultat de la validation. Si l'image est valide, le message contiendra un URI de données représentant l'image.
     */
    public static function imageValidator(?UploadedFile $uploadedFile): array
    {
        if (is_null($uploadedFile)) return ['success' => false, 'data' => "Image manquante."];
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) return ['success' => false, 'data' => "Image incorrecte."];

        $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
        $allowedExtensions = array_map(function ($v) {
            return strtolower(trim($v));
        }, explode(',', $_ENV['ALLOWED_IMAGE_TYPES']));
        if (!in_array($extension, $allowedExtensions)) return ['success' => false, 'data' => sprintf("Format de l'image incorrect (%s).", implode(', ', $allowedExtensions))];

        $size = $uploadedFile->getSize();
        $allowedSize = intval($_ENV['ALLOWED_IMAGE_MAX_SIZE']);
        if ($size > $allowedSize) return ['success' => false, 'data' => sprintf("Taille de l'image incorrecte (max %s).", \App\Libs\SlimEx::human_filesize($allowedSize))];

        $dataUri = \App\Libs\SlimEx::imageToDataUri($uploadedFile->getFilePath(), $extension);
        return ['success' => true, 'data' => $dataUri];
    }

    /**
     * Cette fonction vérifie si l'utilisateur est connecté et renvoie un message d'erreur s'il ne l'est pas.
     * 
     * @param Request request  est un objet de la classe Request dans le framework Slim. Il représente une requête HTTP qui a été reçue par l'application et contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * 
     * @return Si l'attribut `` est `false`, la fonction renverra un message d'erreur avec un code d'état 403 indiquant que l'utilisateur doit être connecté pour effectuer l'opération demandée. Si l'attribut `` est `true`, rien ne sera retourné.
     */
    public static function onlyConnected(Request $request)
    {
        $is_connected = $request->getAttribute('is_connected');
        if (!$is_connected) {
            return SlimEx::sendError(403, "Vous devez être connecté pour effectuer cette opération.");
        }
    }

    /**
     * Cette fonction PHP vérifie si l'utilisateur qui fait la demande est un administrateur et renvoie un message d'erreur si ce n'est pas le cas.
     *
     * @param Request request  est un objet de la classe Request qui contient les informations de la requête HTTP telles que les en-têtes, les paramètres et le corps.
     *
     * @return Si le rôle de l'utilisateur n'est pas 'admin', la fonction renvoie un message d'erreur avec un code d'état 403.
     */
    public static function onlyAdmin(Request $request)
    {
        $owner = $request->getAttribute('user');
        if ($owner->role !== 'admin') {
            return SlimEx::sendError(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }
    }

    /**
     * Cette fonction vérifie si l'utilisateur qui fait la demande est soit un administrateur, soit un travailleur, et renvoie un message d'erreur si ce n'est pas le cas.
     *
     * @param Request request  est un objet de la classe Request qui contient les informations de la requête HTTP telles que les en-têtes, les paramètres et le corps.
     *
     * @return Si le rôle de l'utilisateur n'est pas 'admin' ou 'worker', la fonction renvoie un message d'erreur avec un code d'état 403.
     */
    public static function onlyAdminAndWorkers(Request $request)
    {
        $owner = $request->getAttribute('user');
        if ($owner->role !== 'admin' && $owner->role !== 'worker') {
            return SlimEx::sendError(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }
    }

    /**
     * La fonction génère un mot de passe fort avec une longueur et des jeux de caractères spécifiés, et peut éventuellement ajouter des tirets pour plus de lisibilité.
     *
     * @param length La longueur du mot de passe généré. Par défaut, il est défini sur 9 caractères.
     * @param add_dashes Le paramètre add_dashes est une valeur booléenne qui détermine s'il faut ou non ajouter des tirets au mot de passe généré. Si la valeur est true, des tirets seront ajoutés au mot de passe pour en faciliter la lecture et la mémorisation. Si défini sur false, aucun tiret ne sera ajouté.
     * @param available_sets Ce paramètre est utilisé pour spécifier les jeux de caractères à utiliser pour générer le mot de passe. C'est une chaîne qui peut contenir les caractères suivants :
     *
     * @return un mot de passe fort généré aléatoirement. Si le paramètre  est défini sur true, le mot de passe inclura également des tirets pour plus de lisibilité.
     */
    public static function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        if (!$add_dashes) {
            return $password;
        }

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    /**
     * Cette fonction PHP convertit un fichier image en une chaîne URI de données.
     * 
     * @param string filepath Le paramètre filepath est une chaîne qui représente le chemin d'accès au fichier image que vous souhaitez convertir en URI de données.
     * 
     * @return string une chaîne d'URI de données qui représente le fichier image situé dans le chemin de fichier donné. La chaîne d'URI de données inclut le type d'image (déterminé à partir de l'extension de fichier), les données d'image codées en base64 et le préfixe "data:image" qui l'identifie en tant qu'URI de données.
     */
    public static function imageToDataUri(string $filepath, string $type): string
    {
        $data = file_get_contents($filepath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * La fonction convertit une taille de fichier en octets dans un format lisible par l'homme avec les unités appropriées.
     * 
     * @param size Le paramètre de taille est la taille d'un fichier ou de données en octets qui doivent être convertis dans un format lisible par l'homme.
     * @param precision Le paramètre de précision est un paramètre facultatif qui spécifie le nombre de décimales à inclure dans la chaîne renvoyée. La valeur par défaut est 2.
     * 
     * @return string une chaîne qui représente la taille de fichier donnée dans un format lisible par l'homme avec une précision spécifiée. La taille est convertie dans l'unité appropriée (octets, kilo-octets, mégaoctets, etc.) et arrondie au nombre de décimales spécifié.
     */
    public static function human_filesize($size, $precision = 2): string
    {
        $units = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision) . $units[$i];
    }

    /**
     * Cette fonction PHP récupère l'adresse IP de l'utilisateur.
     * 
     * @return string l'adresse IP de l'utilisateur accédant au site Web.
     */
    public static function getUserIpAddr(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
