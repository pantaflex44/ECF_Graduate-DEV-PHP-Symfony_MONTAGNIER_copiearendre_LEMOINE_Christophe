<?php

namespace App\Libs;

use GdImage;
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
    public static function send_error(int $code, string $message, array $content = []): Response
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
    public static function password_validator(string $password): bool
    {
        return preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', trim($password));
    }

    /**
     * La fonction vérifie si un nom d'affichage donné a une longueur d'au moins 3 caractères.
     *
     * @param string display_name Le paramètre "display_name" est une chaîne qui représente le nom d'affichage d'un utilisateur. La fonction "display_name_validator" prend cette chaîne en entrée et renvoie une valeur booléenne indiquant si le nom d'affichage est valide ou non. Dans ce cas, la fonction vérifie si la longueur du nom d'affichage coupé est
     *
     * @return bool une valeur booléenne, qui est `true` si la longueur de la chaîne `` coupée est supérieure ou égale à 3, et `false` sinon.
     */
    public static function display_name_validator(string $display_name): bool
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
    public static function name_validator(string $name): bool
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
    public static function description_validator(string $description): bool
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
    public static function email_validator(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    /**
     * La fonction `phone_validator` en PHP vérifie si un numéro de téléphone donné est valide selon un modèle d'expression régulière spécifique.
     * 
     * @param string phone Le paramètre phone est une chaîne qui représente un numéro de téléphone.
     * 
     * @return bool une valeur booléenne. Elle renvoie true si le numéro de téléphone passé en argument correspond au modèle d'expression régulière spécifié, et false sinon.
     */
    public static function phone_validator(string $phone): bool
    {
        $regexp = "/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/";
        return !preg_match($regexp, $phone) ? false : true;
    }

    /**
     * Cette fonction PHP valide si un montant donné est supérieur ou égal à zéro.
     * 
     * @param float amount Le paramètre « montant » est un type de données flottant représentant une valeur monétaire. La fonction "amount_validator" vérifie si le montant est supérieur ou égal à zéro, ce qui est un contrôle de validation courant pour les valeurs monétaires.
     * 
     * @return bool La fonction `amount_validator` renvoie une valeur booléenne. Il renverra `true` si le paramètre `` est supérieur ou égal à 0,00, et `false` sinon.
     */
    public static function amount_validator(float $amount): bool
    {
        return ($amount >= 0.00);
    }

    /**
     * La fonction vérifie si une date de sortie donnée est dans le passé.
     * 
     * @param string release_date Le paramètre release_date est une chaîne représentant une date au format "Y-m-d".
     * 
     * @return bool une valeur booléenne. Il renvoie vrai si la date de sortie fournie est dans le passé (avant la date et l'heure actuelles) et faux sinon.
     */
    public static function release_date_validator(string $release_date): bool
    {
        $tz = timezone_open(date_default_timezone_get());
        $dt = date_create_from_format('Y-m-d', $release_date, $tz);
        $now = date_create_immutable("now", $tz);

        return ($dt < $now);
    }

    /**
     * Cette fonction valide un fichier image téléchargé en vérifiant son extension, sa taille et en le convertissant en un URI de données.
     * 
     * @param UploadedFile uploadedFile Une instance de la classe UploadedFile représentant le fichier image téléchargé.
     * @param int max_allowed_size Taille maximale en octets de l'image téléchargée.
     * 
     * @return array un tableau à deux éléments. Le premier élément est une valeur booléenne indiquant si l'image téléchargée est valide ou non. Le deuxième élément est un message de chaîne fournissant plus d'informations sur le résultat de la validation. Si l'image est valide, le message contiendra un URI de données représentant l'image.
     */
    public static function image_validator(?UploadedFile $uploaded_file, int $max_allowed_size = 1024000): array
    {
        if (is_null($uploaded_file)) return ['success' => false, 'data' => "Image manquante."];
        if ($uploaded_file->getError() !== UPLOAD_ERR_OK) return ['success' => false, 'data' => "Image incorrecte."];

        $extension = strtolower(pathinfo($uploaded_file->getClientFilename(), PATHINFO_EXTENSION));
        $allowed_extensions = array_map(function ($v) {
            return strtolower(trim($v));
        }, explode(',', $_ENV['ALLOWED_IMAGE_TYPES']));
        if (!in_array($extension, $allowed_extensions)) return ['success' => false, 'data' => sprintf("Format de l'image incorrect (%s).", implode(', ', $allowed_extensions))];

        $size = $uploaded_file->getSize();
        $allowed_size = intval($_ENV['ALLOWED_IMAGE_MAX_SIZE']);
        if ($max_allowed_size < $allowed_size) $allowed_size = $max_allowed_size;
        if ($size > $allowed_size) return ['success' => false, 'data' => sprintf("Taille de l'image incorrecte (max %s).", \App\Libs\SlimEx::human_filesize($allowed_size))];

        $data_uri = \App\Libs\SlimEx::image_to_data_uri($uploaded_file->getFilePath(), $extension);
        return ['success' => true, 'data' => $data_uri];
    }

    /**
     * Cette fonction vérifie si l'utilisateur est connecté et renvoie un message d'erreur s'il ne l'est pas.
     * 
     * @param Request request  est un objet de la classe Request dans le framework Slim. Il représente une requête HTTP qui a été reçue par l'application et contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     * 
     * @return Si l'attribut `` est `false`, la fonction renverra un message d'erreur avec un code d'état 403 indiquant que l'utilisateur doit être connecté pour effectuer l'opération demandée. Si l'attribut `` est `true`, rien ne sera retourné.
     */
    public static function only_connected(Request $request)
    {
        $is_connected = $request->getAttribute('is_connected');
        if (!$is_connected) {
            return SlimEx::send_error(403, "Vous devez être connecté pour effectuer cette opération.");
        }
    }

    /**
     * Cette fonction PHP vérifie si l'utilisateur qui fait la demande est un administrateur et renvoie un message d'erreur si ce n'est pas le cas.
     *
     * @param Request request  est un objet de la classe Request qui contient les informations de la requête HTTP telles que les en-têtes, les paramètres et le corps.
     *
     * @return Si le rôle de l'utilisateur n'est pas 'admin', la fonction renvoie un message d'erreur avec un code d'état 403.
     */
    public static function only_admin(Request $request)
    {
        $owner = $request->getAttribute('user');
        if ($owner->role !== 'admin') {
            return SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
        }
    }

    /**
     * Cette fonction vérifie si l'utilisateur qui fait la demande est soit un administrateur, soit un travailleur, et renvoie un message d'erreur si ce n'est pas le cas.
     *
     * @param Request request  est un objet de la classe Request qui contient les informations de la requête HTTP telles que les en-têtes, les paramètres et le corps.
     *
     * @return Si le rôle de l'utilisateur n'est pas 'admin' ou 'worker', la fonction renvoie un message d'erreur avec un code d'état 403.
     */
    public static function only_admin_and_workers(Request $request)
    {
        $owner = $request->getAttribute('user');
        if ($owner->role !== 'admin' && $owner->role !== 'worker') {
            return SlimEx::send_error(403, "Vous n'avez pas les droits pour effectuer cette opération.");
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
    public static function generate_strong_password($length = 9, $add_dashes = false, $available_sets = 'luds')
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
    public static function image_to_data_uri(string $filepath, string $type): string
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
    public static function get_user_ip_addr(): string
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

    /**
     * La fonction "strip_accents" prend une chaîne en entrée et renvoie la même chaîne avec tous les caractères accentués remplacés par leurs homologues non accentués.
     * 
     * @param string text Le paramètre "texte" est une chaîne qui représente le texte d'entrée dont vous souhaitez supprimer les accents.
     * 
     * @return string une chaîne avec tous les caractères accentués remplacés par leurs homologues non accentués.
     */
    public static function strip_accents(string $text): string
    {
        $unwanted_array = array(
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A',
            'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E',
            'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
            'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a',
            'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
            'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ğ' => 'G',
            'İ' => 'I', 'Ş' => 'S', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's',
            'ü' => 'u',  'ă' => 'a', 'Ă' => 'A', 'ș' => 's', 'Ș' => 'S',
            'ț' => 't', 'Ț' => 'T'
        );
        return strtr($text, $unwanted_array);
    }

    /**
     * La fonction alpha_numeric_only supprime tous les caractères non alphanumériques d'une chaîne donnée.
     * 
     * @param string text Le paramètre "texte" est une chaîne qui représente le texte d'entrée que vous souhaitez filtrer et supprimer tous les caractères qui ne sont pas alphanumériques ou tiret ("-").
     * 
     * @return string une chaîne dont tous les caractères non alphanumériques et les tirets ont été supprimés.
     */
    public static function alpha_numeric_only(string $text, $allow_spaces = true, $spaces_to_hypens = false): string
    {
        $rule = 'A-Za-z0-9\-';
        if ($allow_spaces) $rule .= '\s';

        $text = preg_replace('/[^' . $rule . ']/', '', $text);

        if ($spaces_to_hypens) {
            $text = str_replace(' ', '-', $text);
            $text = preg_replace('/-+/', '-', $text);
        }

        return $text;
    }

    /**
     * La fonction renvoie l'URL racine de l'API en fonction du protocole de serveur et de l'hôte actuels.
     * 
     * @return string une chaîne qui représente l'URL racine de l'API.
     */
    public static function root_url(): string
    {
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api';
    }

    /**
     * La fonction `console_log` est utilisée pour envoyer une valeur à la console du navigateur en PHP.
     * 
     * @param mixed output Le paramètre de sortie est la valeur ou la variable que vous souhaitez enregistrer dans la console. Il peut s'agir de n'importe quel type de données, tel qu'une chaîne, un nombre, un tableau ou un objet.
     * @param bool with_script_tags Le paramètre "with_script_tags" est une valeur booléenne qui détermine s'il faut ou non envelopper l'instruction du journal de la console dans des balises de script HTML. Si la valeur est true, l'instruction du journal de la console sera enveloppée dans des balises de script. Si la valeur est false, l'instruction de journal de la console ne sera pas enveloppée dans des balises de script.
     */
    public static function console_log(mixed $output, bool $with_script_tags = true)
    {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }

    /**
     * La fonction `uniqid_real` génère un identifiant unique d'une longueur spécifiée à l'aide d'une fonction aléatoire cryptographiquement sécurisée.
     * 
     * @param int lenght Le paramètre `length` spécifie la longueur de l'identifiant unique généré. Par défaut, il est défini sur 13.
     * 
     * @return string une chaîne générée à l'aide d'une fonction aléatoire cryptographiquement sécurisée. La longueur de la chaîne est déterminée par le paramètre ``, qui par défaut est 13 s'il n'est pas spécifié.
     */
    public static function uniqid_real(int $lenght = 13): string
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new \Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

    /**
     * La fonction `image_to_gd` prend un chemin de fichier en entrée et renvoie une ressource d'image GD ou null en fonction du type de fichier.
     * 
     * @param string file Le paramètre `` est une chaîne qui représente le chemin d'accès au fichier image que vous souhaitez convertir en une ressource d'image GD.
     * 
     * @return GdImage|null un objet GdImage ou null.
     */
    public static function image_to_gd(string $file, string $mimetype): array|null
    {
        $source = false;
        $transparent_color = null;

        switch ($mimetype) {
            case 'image/avif':
                $source = imagecreatefromavif($file);
                break;
            case 'image/bmp':
                $source = imagecreatefrombmp($file);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($file);
                $transparent_index = imagecolortransparent($source);
                if ($transparent_index != (-1)) $transparent_color = imagecolorsforindex($source, $transparent_index);
                break;
            case 'image/jpeg':
                $source = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $source = imagecreatefrompng($file);
                imagealphablending($source, true);
                imagesavealpha($source, true);
                break;
            case 'image/tga':
                $source = imagecreatefromtga($file);
                break;
            case 'image/vnd':
                $source = imagecreatefromwbmp($file);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($file);
                break;
            case 'image/x-xbitmap':
                $source = imagecreatefromxbm($file);
                break;
            case 'image/x-xpixmap':
                $source = imagecreatefromxpm($file);
                break;
        }

        return ['mimetype' => $mimetype, 'gd' => $source, 'transparent_color' => $transparent_color];
    }

    /**
     * La fonction prend une image GD ou une valeur booléenne, une chaîne représentant le type d'image et un tableau facultatif pour la couleur transparente, et renvoie l'image GD modifiée ou null si l'image d'entrée est fausse.
     * 
     * @param GdImage img Le paramètre `` est de type `GdImage|bool`, ce qui signifie qu'il peut accepter soit un objet `GdImage` soit une valeur booléenne. Il représente l'image que vous souhaitez traiter.
     * @param string type Le paramètre `` est une chaîne qui représente le type d'image. Il peut s'agir de l'une des valeurs suivantes :
     * @param array transparent_color Le paramètre `transparent_color` est un tableau facultatif qui contient les valeurs RVB pour la couleur transparente. Il a trois touches : "rouge", "vert" et "bleu", qui représentent l'intensité de chaque composante de couleur. Ce paramètre n'est utilisé que lorsque le `` est `'image/gif'`.
     * 
     * @return GdImage|null la variable ``, qui est de type `GdImage|null`.
     */
    public static function typed_gd(GdImage|bool $img, string $mimetype, array|null $transparent_color = null): GdImage|bool
    {
        if (!$img) return false;

        switch ($mimetype)
        {
            case 'image/png':
                imagealphablending($img, false);
                imagesavealpha($img, true);
                break;
            case 'image/gif':
                $transparent_new = imagecolorallocate($img, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                $transparent_new_index = imagecolortransparent($img, $transparent_new);
                imagefill($img, 0, 0, $transparent_new_index);
        }

        return $img;
    }

    /**
     * La fonction `gd_to_content` prend une ressource d'image GD ou une valeur booléenne et renvoie le contenu de l'image sous forme de chaîne ou de valeur booléenne.
     * 
     * @param GdImage img Le paramètre `` est de type `GdImage|bool`. Il peut accepter soit un objet `GdImage` soit une valeur booléenne.
     * 
     * @return string|bool une chaîne ou une valeur booléenne.
     */
    public static function gd_to_content(GdImage|bool $img, string $mimetype = 'image/png'): string|bool
    {
        if ($img === false) return false;

        $stream = fopen('php://memory', 'r+');
        
        switch ($mimetype)
        {
            case 'image/avif':
                imageavif($img, $stream);
                break;
            case 'image/bmp':
                imagebmp($img, $stream);
                break;
            case 'image/gif':
                imagegif($img, $stream);
                break;
            case 'image/jpeg':
                imagejpeg($img, $stream);
                break;
            case 'image/vnd':
                imagewbmp($img, $stream);
                break;
            case 'image/webp':
                imagewebp($img, $stream);
                break;
            case 'image/x-xbitmap':
                imagexbm($img, $stream);
                break;
            default:
                imagepng($img, $stream);
        }
        
        rewind($stream);
        $content = stream_get_contents($stream);
        
        fclose($stream);
        
        return $content;
    }

    /**
     * La fonction `image_resize` redimensionne un fichier image à une largeur et une hauteur spécifiées à l'aide de la bibliothèque GD de PHP.
     * 
     * @param string file Le paramètre `file` est une chaîne qui représente le chemin d'accès au fichier image que vous souhaitez redimensionner.
     * @param int new_width Le paramètre `new_width` est un entier qui représente la largeur souhaitée de l'image redimensionnée.
     * @param int new_height Le paramètre "new_height" est un entier qui représente la hauteur souhaitée de l'image redimensionnée.
     * 
     * @return GdImage|bool un objet GdImage ou false.
     */
    public static function image_resize(string $file, string $mimetype, int $new_width, int $new_height): GdImage|bool
    {
        list($width, $height) = getimagesize($file);

        $source = SlimEx::image_to_gd($file, $mimetype);
        if ($source === false || ($source['gd'] ?? false) === false) return false;
        
        $thumb = Slimex::typed_gd(imagecreatetruecolor($new_width, $new_height), $source['mimetype'], $source['transparent_color']);
        if ($thumb === false) return false;

        imagecopyresized($thumb, $source['gd'], 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($source['gd']);
        
        return $thumb;
    }

    /**
     * La fonction `image_resize_scale` redimensionne un fichier image d'un pourcentage donné et renvoie le contenu de l'image redimensionnée.
     * 
     * @param string file Le paramètre "file" est une chaîne qui représente le chemin d'accès au fichier image que vous souhaitez redimensionner.
     * @param int percent Le paramètre "pourcentage" est un entier qui représente le pourcentage par lequel l'image doit être redimensionnée. Il détermine la nouvelle largeur et la hauteur de l'image en fonction des dimensions d'origine. Par exemple, si le pourcentage est défini sur 50, l'image sera redimensionnée à la moitié de sa taille d'origine.
     * 
     * @return string|false une chaîne ou faux.
     */
    public static function image_resize_scale(string $file, string $mimetype, int $percent): string|false
    {
        list($width, $height) = getimagesize($file);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;
        $new_width = $width * ($percent / 100);
        $new_height = $height * ($percent / 100);

        $thumb = Slimex::image_resize($file, $mimetype, $new_width, $new_height);
        $content = Slimex::gd_to_content($thumb, $mimetype);

        return $content;
    }

    /**
     * La fonction `image_resize_wh` redimensionne une image tout en conservant son rapport d'aspect en fonction de la largeur et de la hauteur maximales fournies.
     * 
     * @param string file Le paramètre file est une chaîne qui représente le chemin d'accès au fichier image que vous souhaitez redimensionner.
     * @param string mimetype Le paramètre `mimetype` est une chaîne qui représente le type MIME du fichier image. Les types MIME sont utilisés pour identifier le type de données contenues dans un fichier. Par exemple, les types MIME courants pour les images incluent "image/jpeg", "image/png" et "image/gif".
     * @param int max_width La largeur maximale que l'image redimensionnée doit avoir.
     * @param int max_height Le paramètre `max_height` est la hauteur maximale souhaitée pour l'image redimensionnée.
     * 
     * @return string|false une chaîne ou faux.
     */
    public static function image_resize_wh(string $file, string $mimetype, int $max_width, int $max_height): string|false
    {
        list($width, $height) = getimagesize($file);
        $new_width = $max_width != 0 ? $max_width : $width;
        $new_height = $max_height != 0 ? $max_height : $height;
        if ($width > $height && $new_height < $height) {
            $new_height = $height / ($width / $new_width);
        } else if ($width < $height && $new_width < $width) {
            $new_width = $width / ($height / $new_height);
        } else {
            $new_width = $width;
            $new_height = $height;
        }

        $thumb = Slimex::image_resize($file, $mimetype, $new_width, $new_height);
        $content = Slimex::gd_to_content($thumb, $mimetype);

        return $content;
    }
}
