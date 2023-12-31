<?php

namespace App\Libs;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class Offers
{

    /* Le code ci-dessus définit un tableau qui contient des informations sur divers filtres pouvant être appliqués à un ensemble de données. Chaque filtre est identifié par une clé (par exemple 'name', 'description', 'doors', etc.) et a une valeur correspondante qui est un tableau avec deux clés : 'filter' et 'args'. */
    public static array $all_filters = [
        'active'            => ['filter' => 'filter_multi', 'args' => [null, true, null]],
        'name'              => ['filter' => 'filter_multi', 'args' => [null, false, null]],
        'description'       => ['filter' => 'filter_multi', 'args' => [null, false, null]],
        'doors'             => ['filter' => 'filter_multi', 'args' => ['informations', true, null]],
        'color'             => ['filter' => 'filter_multi', 'args' => ['informations', true, null]],
        'brand'             => ['filter' => 'filter_multi', 'args' => ['informations', false, null]],
        'type'              => ['filter' => 'filter_multi', 'args' => ['informations', true, null]],
        'fuel'              => ['filter' => 'filter_multi', 'args' => ['informations', true, null]],
        'model'             => ['filter' => 'filter_multi', 'args' => ['informations', false, null]],
        'sites'             => ['filter' => 'filter_multi', 'args' => ['informations', true, null]],
        'gearbox'           => ['filter' => 'filter_multi', 'args' => ['informations', false, null]],

        'din'               => ['filter' => 'filter_range', 'args' => ['informations', false, null]],
        'fiscal'            => ['filter' => 'filter_range', 'args' => ['informations', false, null]],
        'mileage'           => ['filter' => 'filter_range', 'args' => [null, false, null]],
        'price'             => ['filter' => 'filter_range', 'args' => [null, true, null]],
        'release_date'      => ['filter' => 'filter_range', 'args' => [null, false, 'YEAR']],

        'equipments_list'   => ['filter' => 'filter_json_array', 'args' => []],
    ];

    /* Le code ci-dessus définit un tableau appelé `` en PHP. Ce tableau contient divers filtres et leurs fonctions de filtre et arguments correspondants. Chaque filtre est associé à une fonction de filtrage et à un tableau d'arguments. */
    public static array $all_filters_limits = [
        'name'              => ['limits' => 'filters_limits_nothing', 'column' => null, 'label' => "Rechercher dans le titre", 'values_descriptor' => [], 'value' => ''],
        'description'       => ['limits' => 'filters_limits_nothing', 'column' => null, 'label' => "Dans la description", 'values_descriptor' => [], 'value' => ''],

        'active'            => ['limits' => 'filters_limits_multi', 'column' => null, 'label' => "Etat des annonces", 'values_descriptor' => [0 => 'Désactivées', 1 => 'En ligne'], 'value' => ''],
        'brand'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Marque", 'values_descriptor' => [], 'value' => ''],
        'model'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Modèle", 'values_descriptor' => [], 'value' => ''],
        'type'              => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Carrosserie", 'values_descriptor' => [], 'value' => ''],
        'fuel'              => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Carburant", 'values_descriptor' => [], 'value' => ''],
        'gearbox'           => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Boite de vitesse", 'values_descriptor' => [], 'value' => ''],
        'color'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Couleur", 'values_descriptor' => [], 'value' => ''],
        'doors'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Nombre de portes", 'values_descriptor' => [], 'value' => ''],
        'sites'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Nombre de places", 'values_descriptor' => [], 'value' => ''],

        'din'               => ['limits' => 'filters_limits_range', 'column' => 'informations', 'label' => "Puissance", 'values_descriptor' => [], 'value' => ''],
        'fiscal'            => ['limits' => 'filters_limits_range', 'column' => 'informations', 'label' => "Puissance fiscale", 'values_descriptor' => [], 'value' => ''],
        'mileage'           => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Kilométrage", 'values_descriptor' => [], 'value' => ''],
        'release_date'      => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Date de mise en service", 'values_descriptor' => [], 'value' => ''],
        'price'             => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Prix de vente", 'values_descriptor' => [], 'value' => ''],

        'equipments_list'   => ['limits' => 'filters_limits_array', 'column' => null, 'label' => "Equipements", 'values_descriptor' => [], 'value' => ''],
    ];

    /**
     * La fonction supprime les préfixes "equipments_list_" et "informations_" d'une chaîne donnée.
     * 
     * @param string name Le paramètre "name" est une chaîne qui représente le nom d'une variable ou d'un champ.
     * 
     * @return string La fonction `filter_name_converter` prend un paramètre de chaîne `` et en renvoie une version modifiée. Les modifications impliquent la suppression des sous-chaînes "equipments_list_" et "informations_" de la chaîne d'entrée. La chaîne résultante est ensuite renvoyée. Par conséquent, la fonction renvoie une chaîne.
     */
    public static function filter_name_converter(string $name): string
    {
        return
            str_replace(
                'equipments_list_',
                '',
                str_replace('informations_', '', $name)
            );
    }

    /**
     * Il s'agit d'une fonction PHP qui filtre une valeur de chaîne en fonction d'un nom de colonne donné, avec des options de correspondance et de transformation exactes ou partielles.
     * 
     * @param string name Nom du champ sur lequel filtrer.
     * @param string value La valeur à filtrer. Il s'agit d'une chaîne pouvant contenir plusieurs valeurs séparées par des virgules.
     * @param string column Le paramètre de colonne est un paramètre facultatif qui spécifie le nom de la colonne JSON dans laquelle la valeur doit être recherchée. S'il n'est pas fourni, la fonction recherchera la valeur dans le paramètre de nom spécifié.
     * @param bool exact Un paramètre booléen qui détermine si le filtre doit être une correspondance exacte ou une correspondance partielle. S'il est défini sur true, le filtre recherchera une correspondance exacte. S'il est défini sur false, le filtre recherchera une correspondance partielle.
     * @param string transform Le paramètre "transform" est un paramètre facultatif qui spécifie une fonction SQL à appliquer à la colonne filtrée. Il peut être utilisé pour modifier les données avant le filtrage, par exemple en appliquant une fonction de chaîne ou en convertissant les données en un type de données différent. Si ce paramètre n'est pas fourni, la colonne sera
     * 
     * @return array Un tableau avec deux clés : 'sql' et 'values'. La clé 'sql' contient une chaîne de conditions SQL pour filtrer une requête de base de données en fonction des paramètres fournis, et la clé 'values' contient un tableau de valeurs de paramètres à utiliser dans la requête.
     */
    public static function filter_multi(string $name, string $value, string|null $column = null, bool $exact = true, string|null $transform = null): array
    {
        $w = [];
        $v = [];
        $list = explode(',', trim($value));
        for ($i = 0; $i < count($list); $i++) {
            $val = trim(strval($list[$i]));
            if (strlen($val) === 0) continue;

            $c = !is_null($column) ? "$column->>'$.$name'" : "$name";
            if (!is_null($transform)) $c = "$transform($c)";

            $w[] = (!$exact ? "LOWER(" : "") . $c . (!$exact ? ")" : "") . " " . ($exact ? "=" : "LIKE") . " :$name$i";
            $v[$name . $i] = $val;

            if (!$exact) $v[$name . $i] = "%" . strtolower($v[$name . $i]) . "%";
        }
        return ['sql' => count($w) > 0 ? implode(' OR ', $w) : null, 'values' => $v];
    }

    /**
     * La fonction filtre un tableau JSON en fonction d'un nom et d'une valeur donnés.
     * 
     * @param string name Le paramètre "name" est une chaîne qui représente le nom du champ JSON dans la table de base de données sur laquelle vous souhaitez filtrer.
     * @param string value Le paramètre `` est une chaîne qui contient des valeurs séparées par des virgules. Ces valeurs seront utilisées pour filtrer un tableau JSON en fonction du paramètre ``.
     * 
     * @return array un tableau avec deux clés : 'sql' et 'values'. La clé 'sql' contient une chaîne qui représente une condition SQL, qui est construite en fonction des paramètres d'entrée. La clé 'values' contient un tableau associatif dans lequel les clés sont construites en fonction du paramètre d'entrée et d'un index, et les valeurs sont les valeurs correspondantes à utiliser dans la condition SQL.
     */
    public static function filter_json_array(string $name, string $value): array
    {
        $w = [];
        $v = [];
        $list = explode(',', trim($value));
        for ($i = 0; $i < count($list); $i++) {
            $val = trim(strval($list[$i]));
            if (strlen($val) === 0) continue;
            $w[] = "JSON_SEARCH(LOWER($name), 'all', :$name$i) IS NOT NULL";
            $v[$name . $i] = "%" . strtolower($val) . "%";
        }
        return ['sql' => count($w) > 0 ? implode(' OR ', $w) : null, 'values' => $v];
    }

    /**
     * La fonction filtre une plage de valeurs en fonction d'une colonne ou d'un nom donné et renvoie une requête SQL et ses valeurs.
     * 
     * @param string name Le nom de la colonne à filtrer.
     * @param string value La valeur pour filtrer la plage. Il doit s'agir d'une chaîne contenant deux valeurs séparées par un point-virgule (;) représentant le début et la fin de la plage. Par exemple : "10;20".
     * @param string column Nom de la colonne de la table de base de données sur laquelle filtrer. Si nul, le filtre sera appliqué directement sur le paramètre .
     * @param is_float Le paramètre is_float est une valeur booléenne qui détermine si les valeurs de plage doivent être traitées comme des flottants ou des entiers. S'il est défini sur true, les valeurs seront converties en flottants, sinon elles seront converties en entiers.
     * @param string transform Le paramètre "transform" est un paramètre facultatif qui spécifie une fonction SQL à appliquer à la colonne avant le filtrage. Il peut être utilisé pour transformer les données dans la colonne avant d'appliquer le filtre. S'il n'est pas fourni, le filtre sera appliqué directement à la colonne.
     * 
     * @return array Un tableau avec deux clés : 'sql' et 'values'. La clé 'sql' contient une chaîne avec une condition SQL qui vérifie si une valeur de colonne ou de champ donnée se situe dans une plage spécifiée. La clé 'values' contient un tableau avec les valeurs des paramètres qui seront utilisées pour lier les espaces réservés dans la requête SQL.
     */
    public static function filter_range(string $name, string $value, string|null $column = null, $is_float = false, string|null $transform = null): array
    {
        $w = [];
        $v = [];
        $between = explode(';', trim($value));
        if (count($between) === 2) {
            $start = !$is_float ? intval($between[0]) : floatval($between[0]);
            $end = !$is_float ? intval($between[1]) : floatval($between[1]);
            if ($start > $end) $end = $start;

            $c = !is_null($column) ? "$column->>'$.$name'" : "$name";
            if (!is_null($transform)) $c = "$transform($c)";

            $w[] = "($c >= :" . $name . "_min AND $c <= :" . $name . "_max)";
            $v[$name . "_min"] = $start;
            $v[$name . "_max"] = $end;
        }
        return ['sql' => count($w) > 0 ? implode(' OR ', $w) : null, 'values' => $v];
    }

    /**
     * Cette fonction PHP convertit un tableau de filtres en une requête MySQL.
     * 
     * @param array filters Un tableau de filtres à convertir en conditions de requête MySQL.
     * 
     * @return array Tableau contenant les conditions de requête SQL et leurs valeurs correspondantes, généré à partir du tableau d'entrée des filtres.
     */
    public static function filters_to_mysql(array$filters): array
    {
        $where = ['sql' => [], 'values' => []];

        $filters = array_change_key_case($filters, CASE_LOWER);
        foreach ($filters as $name => $value) {
            try {
                $name = Offers::filter_name_converter($name);
                if (array_key_exists($name, Offers::$all_filters)) {
                    $item = Offers::$all_filters[$name];
                    $rm = new \ReflectionMethod(__CLASS__, $item['filter']);
                    if (!is_null($rm) && $rm->isStatic()) {
                        $result = call_user_func_array(__CLASS__ . "::" . $item['filter'], array_merge([$name, $value], $item['args']));
                        $where['sql'][] = $result['sql'];
                        $where['values'] = array_merge($where['values'], $result['values']);
                    }
                }
            } catch (Exception $ex) {
            }
        }

        return $where;
    }

    /**
     * Cette fonction récupère une liste d'offres à partir d'une base de données en fonction de certains filtres et paramètres de pagination.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est utilisée pour récupérer des informations sur la requête HTTP en cours.
     * @param bool active_only Une valeur booléenne indiquant s'il faut récupérer uniquement les offres actives ou non.
     * @param array filters Un tableau de filtres à appliquer à la requête SQL. La fonction `filters_to_mysql()` est utilisée pour convertir les filtres en une clause SQL WHERE.
     * @param int page Le numéro de page des résultats à récupérer. La valeur par défaut est 1.
     * @param int per_page Le nombre de résultats à afficher par page dans la liste paginée.
     * 
     * @return array un tableau avec les clés et valeurs suivantes :
     * - 'page' : le numéro de la page courante
     * - 'per_page' : le nombre d'éléments par page
     * - 'total_page' : le nombre total de pages
     * - 'offset' : le décalage de la page courante
     * - 'count' : le nombre total d'éléments
     * - 'data' : un tableau d'offres, chaque offre étant un tableau
     */
    public static function list(Request $request, bool $active_only, array $filters, array $sorters, int $page = 1, int $per_page = 20): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT COUNT(*) FROM offers";
        $where = Offers::filters_to_mysql($filters, $active_only);
        $active = [];
        $wfFiltered = [];
        if (count($where['sql']) > 0) {
            $firstFilter = array_filter($where['sql'], function ($v, $k) {
                return !is_null($v);
            }, ARRAY_FILTER_USE_BOTH);

            foreach ($firstFilter as $k => $v) {
                if (str_starts_with($v, 'active') || str_starts_with($v, '(active')) {
                    $active[] = $v;
                } else {
                    $wfFiltered[] = $v;
                }
            }

            /*if($active_only) {
                $active = ['active = :active1'];
                unset($where['values']['active0']);
            }*/

            $wf = count($wfFiltered) > 0 ? trim(implode(' AND ', $wfFiltered)) : '';
            if (count($active) > 0) {
                $wf  = '(' . (trim(implode(' OR ', $active))) . ')' . (strlen($wf) > 0 ? ' AND (' . $wf . ')' : '');
            }

            if (strlen($wf) > 0) {
                if (stripos($sql, "WHERE") !== false) {
                    $sql .= " AND (";
                } else {
                    $sql .= " WHERE (";
                }
                $sql .= $wf;
                $sql .= ")";
            }
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($where['values']);
        $count = $stmt->fetchColumn() ?? -1;
        if ($per_page > $count) {
            $per_page = $count;
            $page = 1;
        }
        if ($per_page < 1) $per_page = 1;
        $offset = ($page * $per_page) - $per_page;
        $total_page = ceil($count / $per_page);

        $sql = str_replace("COUNT(*)", "*, DATE_FORMAT(release_date, '%Y-%m') as release_date", $sql);

        $sql .= " ORDER BY ";
        $s = [];
        foreach ($sorters as $sorter_type => $sorter_order) {
            $type = trim($db->quote($sorter_type), "'");
            $order = strtoupper(trim($sorter_order));
            if ($order === 'ASC' || $order === 'DESC') {
                $s[] = $type . " " . $order;
                if ($type === 'dt') $s[] = "id " . $order;
            }
        };
        if (count($s) === 0) {
            $sql .= "dt DESC, id DESC";
        } else {
            $sql .= join(', ', $s);
        }

        $sql .= " LIMIT " . $per_page . " OFFSET " . $offset;
        $stmt = $db->prepare($sql);
        $stmt->execute($where['values']);
        $result = $stmt->fetchAll() ?? [];

        $ret = [
            'page' => $page,
            'per_page' => $per_page,
            'total_page' => $total_page,
            'offset' => $offset,
            'count' => $count,
            'data' => []
        ];
        if ($request->getAttribute('debug', false)) {
            $ret = array_merge($ret, ['query' => ['sql' => $sql, 'filterValues' => $where['values'], 'filters' => $wfFiltered, 'activeStates' => $active]]);
        }

        foreach ($result as $offer) {
            $offer['equipments_list'] = json_decode($offer['equipments_list']);
            $offer['informations'] = json_decode($offer['informations']);
            $offer['price'] = floatval($offer['price']);

            $image = $offer['image'];
            unset($offer['image']);
            $offer['gallery'] = [];
            if (trim($image) !== "") {
                $dir = "./data/gallery/$image";
                if (is_dir($dir) && is_readable($dir)) {
                    $files = scandir($dir, SCANDIR_SORT_ASCENDING);
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $f = "$dir/$file";
                            if (is_file($f) && $file !== "." && $file != "..") {
                                $img_name = base64_encode($file);
                                $offer['gallery'][] = Slimex::root_url() . "/image/$image/$img_name";
                            }
                        }
                    }
                }
            }

            $ret['data'][] = $offer;
        }

        return $ret;
    }

    /**
     * La fonction filter_limits_multi récupère des valeurs distinctes d'une colonne spécifique dans une table de base de données et les renvoie comme options de filtrage.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui est généralement utilisée pour gérer les requêtes HTTP dans une application Web. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et les paramètres de requête.
     * @param string name Le paramètre `name` est une chaîne qui représente le nom du filtre ou de la limite. Il est utilisé pour spécifier la colonne ou le champ de la table de base de données sur lequel vous souhaitez filtrer ou limiter les résultats.
     * @param string column Le paramètre "column" est un paramètre facultatif qui spécifie le nom de la colonne dans la table de la base de données. Si une valeur est fournie pour le paramètre "column", elle sera utilisée pour construire la requête SQL en l'ajoutant au nom de la colonne et au paramètre name. Si aucune valeur n'est fournie pour le "
     * 
     * @return array Un tableau est retourné avec deux clés : 'key' et 'options'. La valeur de 'key' est la concaténation des variables  et  (si  n'est pas null), sinon c'est juste la valeur de . La valeur de 'options' est un tableau contenant les valeurs distinctes de la colonne  de la table 'gvp.offers' dans
     */
    public static function filters_limits_multi(Request $request, string $name, string|null $column = null): array
    {
        $db = $request->getAttribute('db');

        $c = !is_null($column) ? "$column->>'$.$name'" : $name;
        $key = !is_null($column) ? $column . "_" . $name : $name;

        $sql = "SELECT DISTINCT $c FROM gvp.offers";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $options = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?? [];

        return ['key' => $key, 'component' => 'choices', 'options' => $options];
    }

    /**
     * La fonction `filters_limits_range` récupère les valeurs minimales et maximales d'une colonne spécifiée à partir d'une table de base de données.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui est utilisée pour gérer les requêtes HTTP dans l'application.
     * @param string name Le paramètre `name` est une chaîne qui représente le nom de la colonne dans la table de base de données à partir de laquelle vous souhaitez récupérer les valeurs minimale et maximale.
     * @param string column Le paramètre `` est un paramètre facultatif qui spécifie le nom de la colonne dans la table de la base de données. Si une valeur est fournie pour ``, elle sera utilisée dans la requête SQL pour récupérer les valeurs minimale et maximale de la colonne spécifiée. Si `` est `null`, la requête SQL sera
     * 
     * @return array Un tableau est renvoyé avec les clés et les valeurs suivantes :
     */
    public static function filters_limits_range(Request $request, string $name, string|null $column = null): array
    {
        $db = $request->getAttribute('db');

        $c = !is_null($column) ? "MIN($column->'$.$name') as min, MAX($column->'$.$name') as max" : "MIN($name) as min, MAX($name) as max";
        $key = !is_null($column) ? $column . "_" . $name : $name;

        $sql = "SELECT DISTINCT $c FROM gvp.offers";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetchAll() ?? [];
        $min = 0;
        $max = 0;
        if (count($row) == 1 && array_key_exists('min', $row[0]) && array_key_exists('max', $row[0])) {
            $min = floatval($row[0]['min']);
            $max = floatval($row[0]['max']);
        }

        return ['key' => $key, 'component' => 'minmax_range', 'min' => $min, 'max' => $max];
    }

    /**
     * La fonction `filters_limits_array` récupère une liste distincte de valeurs à partir d'une colonne spécifique dans une table de base de données et applique certaines transformations à chaque valeur avant de renvoyer la liste.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui est généralement utilisée pour gérer les requêtes HTTP dans une application Web. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et les paramètres de requête.
     * @param string name Le paramètre name est une chaîne qui représente le nom du filtre ou de la limite. Il est utilisé pour construire la requête SQL et comme clé dans le tableau renvoyé.
     * @param string column Le paramètre `` est un paramètre facultatif qui spécifie le nom de la colonne dans la table de la base de données. Si une valeur est fournie pour ``, elle sera utilisée pour construire la requête SQL en l'ajoutant au paramètre ``. Si `` est `null`, alors seul le `
     * 
     * @return array Un tableau est renvoyé avec deux clés : 'key' et 'list'. La valeur de 'key' est la concaténation des variables  et  (si  n'est pas null), sinon c'est juste la valeur de . La valeur de 'list' est un tableau de valeurs distinctes obtenues à partir d'une requête de base de données. Ces valeurs sont traitées en appliquant une série de transformations
     */
    public static function filters_limits_array(Request $request, string $name, string|null $column = null): array
    {
        $db = $request->getAttribute('db');

        $c = !is_null($column) ? "$column->>'$.$name'" : $name;
        $key = !is_null($column) ? $column . "_" . $name : $name;

        $sql = "SELECT DISTINCT $c FROM gvp.offers";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?? [];
        if (count($list) > 0) {
            $list = array_map(function ($item) {
                $item = strtolower(trim($item));
                $item = Slimex::strip_accents($item);
                $item = Slimex::alpha_numeric_only($item);
                return $item;
            }, json_decode($list[0]));
        }

        return ['key' => $key, 'component' => 'text_with_autocomplete', 'list' => $list];
    }

    /**
     * La fonction "filters_limits_nothing" prend une requête, un nom et une colonne facultative, et renvoie un tableau avec une clé basée sur la colonne et le nom.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui représente une requête HTTP faite au serveur. Il contient des informations sur la demande telles que la méthode de demande, les en-têtes et les données de la demande.
     * @param string name Le paramètre "name" est une chaîne qui représente le nom du filtre ou de la limite. Il est utilisé pour créer une clé unique pour le filtre ou la limite.
     * @param string column Le paramètre "colonne" est un paramètre facultatif de type chaîne. Il peut s'agir d'une valeur de chaîne ou de null.
     * 
     * @return array Un tableau est renvoyé avec une seule paire clé-valeur. La clé est déterminée en concaténant les valeurs des variables `` et `` (si `` n'est pas nul), et la valeur est la chaîne concaténée.
     */
    public static function filters_limits_nothing(Request $request, string $name, string|null $column = null): array
    {
        $key = !is_null($column) ? $column . "_" . $name : $name;

        return ['key' => $key, 'component' => 'text_free'];
    }

    /**
     * La fonction "filters_limits" parcourt une liste de filtres et leurs limites correspondantes, appelle les méthodes de filtrage et renvoie un tableau de résultats de filtrage.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée pour récupérer des informations sur la requête HTTP en cours. Il contient des données telles que la méthode de requête, les en-têtes, les paramètres de requête et le corps de la requête. Dans cet extrait de code, le paramètre  est transmis aux méthodes de filtrage avec
     * 
     * @return array un ensemble de filtres.
     */
    public static function filters_limits(Request $request): array
    {
        $filters = [];

        foreach (Offers::$all_filters_limits as $name => $item) {
            try {
                $rm = new \ReflectionMethod(__CLASS__, $item['limits']);
                if (!is_null($rm) && $rm->isStatic()) {
                    $filters[$name] = array_merge(call_user_func_array(__CLASS__ . "::" . $item['limits'], [$request, $name, $item['column']]), ['label' => $item['label'], 'values_descriptor' => $item['values_descriptor'], 'value' => $item['value']]);
                }
            } catch (Exception $ex) {
            }
        }

        return $filters;
    }

    /**
     * La fonction `get_image` récupère le contenu et le type de contenu d'un fichier image en fonction de l'ID et du nom de fichier fournis.
     * 
     * @param Request request Le paramètre `` est une instance de la classe `Request`, qui est généralement utilisée pour gérer les requêtes HTTP dans une application Web. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et les paramètres de requête.
     * @param string id Le paramètre `id` est une chaîne qui représente l'ID de la galerie. Il est utilisé pour identifier la galerie spécifique à partir de laquelle l'image est récupérée.
     * @param string file Le paramètre `file` est une chaîne qui représente le nom du fichier à récupérer dans la galerie. Il devrait être au format encodé en base64.
     * 
     * @return array|null un tableau avec deux clés : 'data' et 'content-type'. La valeur de 'data' est le contenu du fichier et la valeur de 'content-type' est le type MIME du fichier. Si le fichier n'existe pas ou si le répertoire n'est pas lisible, la fonction renvoie null.
     */
    public static function get_image(Request $request, string $id, string $file, array $size): array|null
    {
        $file = base64_decode($file);
        $dir = "./data/gallery/$id";
        if (is_dir($dir) && is_readable($dir)) {
            $f = "$dir/$file";
            if (is_file($f) && is_readable($f)) {
                $finfo = @finfo_open(FILEINFO_MIME);
                $mimetype = @finfo_file($finfo, $f);
                @finfo_close($finfo);
                if ($mimetype === false) $mimetype = '';
                $mimetype = strtolower(trim(explode(';', $mimetype)[0]));

                $content = '';
                switch ($size['mode'] ?? '') {
                    case 'scale':
                        $content = \App\Libs\SlimEx::image_resize_scale($f, $mimetype, $size['percent'] ?? 100);
                        break;
                    case 'resize':
                        $content = \App\Libs\SlimEx::image_resize_wh($f, $mimetype, $size['width'] ?? 0, $size['height'] ?? 0);
                        break;
                }

                if ($content === false || $content === '') $content = @file_get_contents($f);

                return ['data' => $content, 'content-type' => $mimetype];
            }
        }
        return null;
    }

    /**
     * La fonction ajoute une nouvelle offre à la base de données avec les informations fournies et enregistre les images de la galerie dans un répertoire correspondant.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée dans les applications Web pour gérer les requêtes HTTP et accéder aux données de requête telles que les en-têtes, les paramètres de requête et le corps de la requête.
     * @param string name Le nom de l'offre.
     * @param string description Le paramètre "description" est une chaîne qui représente la description de l'offre. Il fournit des informations supplémentaires ou des détails sur l'offre.
     * @param float price Le paramètre "prix" est une valeur flottante représentant le prix de l'offre.
     * @param string release_date Le paramètre release_date est une chaîne qui représente la date de sortie de l'offre.
     * @param int mileage Le paramètre "kilométrage" représente le nombre de miles parcourus par un véhicule. Il est de type entier.
     * @param array gallery Un tableau de fichiers représentant les images à télécharger pour l'offre.
     * @param array informations Un tableau contenant des informations sur l'offre. Chaque élément du tableau représente une information et peut être de n'importe quel type de données.
     * @param array equipments_list Un tableau contenant la liste des équipements de l'offre.
     * 
     * @return bool une valeur booléenne. Il renvoie vrai si l'insertion dans la base de données est réussie et que le répertoire de la galerie est créé et que tous les fichiers sont téléchargés avec succès. Elle renvoie faux s'il y a une erreur dans la création du répertoire de la galerie ou si l'insertion dans la base de données échoue.
     */
    public static function add(Request $request, string $name, string $description, float $price, string $release_date, int $mileage, array $gallery, array $informations, array $equipments_list): bool
    {
        $db = $request->getAttribute('db');

        $informations = json_encode($informations);
        $equipments_list = json_encode($equipments_list);

        $id = Slimex::uniqid_real();
        $dir = "./data/gallery/$id";
        while (is_dir($dir)) {
            $id = Slimex::uniqid_real();
            $dir = "./data/gallery/$id";
        }

        $sql = "INSERT INTO offers (active, name, description, price, release_date, mileage, image, informations, equipments_list, dt) VALUES (0, :name, :description, :price, :release_date, :mileage, :image, :informations, :equipments_list, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name, ':description' => $description, ':price' => $price, ':release_date' => $release_date, ':mileage' => $mileage, ':image' => $id, ':informations' => $informations, ':equipments_list' => $equipments_list]);

        if ($stmt->rowCount() === 1) {
            if (!mkdir($dir, 0744)) {
                $sql = "DELETE FROM offers WHERE image = :image";
                $stmt = $db->prepare($sql);
                $stmt->execute([':image' => $id]);

                return false;
            }

            for ($i = 0; $i < count($gallery); $i++) {
                $file = $gallery[$i];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $index = $i + 1;
                $filepath = "$dir/$index.$ext";
                move_uploaded_file($file, $filepath);
            }

            return true;
        }

        return false;
    }

    /**
     * La fonction met à jour un enregistrement dans la base de données avec les informations fournies et met également à jour les fichiers image associés dans la galerie.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée pour récupérer des informations sur la requête HTTP en cours. Il est utilisé ici pour accéder à l'objet de connexion à la base de données.
     * @param int id Le paramètre `id` est un entier qui représente l'ID de l'offre qui doit être mise à jour dans la base de données.
     * @param string name Le nom de l'offre.
     * @param string description Le paramètre description est une chaîne qui représente la description mise à jour d'une offre.
     * @param float price Le paramètre price est de type float et représente le prix de l'offre.
     * @param string release_date Le paramètre release_date est une chaîne qui représente la date de sortie de l'offre.
     * @param int mileage Le paramètre « kilométrage » représente le kilométrage d'un véhicule. Il s'agit d'une valeur entière qui indique le nombre de kilomètres parcourus par le véhicule.
     * @param array gallery Un tableau de fichiers représentant les images de la galerie. Chaque fichier doit être une chaîne représentant le chemin du fichier ou un objet fichier.
     * @param array informations Un tableau contenant des informations sur l'offre. Chaque élément du tableau représente une information et peut être de n'importe quel type de données.
     * @param array equipments_list Un tableau contenant la liste des équipements de l'offre. Chaque élément du tableau représente un élément d'équipement unique.
     * 
     * @return bool une valeur booléenne. Elle renvoie true si l'opération de mise à jour a réussi et false sinon.
     */
    public static function update(Request $request, int $id, string $name, string $description, float $price, string $release_date, int $mileage, array $gallery, array $informations, array $equipments_list): bool
    {
        $db = $request->getAttribute('db');

        $informations = json_encode($informations);
        $equipments_list = json_encode($equipments_list);

        $sql = "SELECT image FROM offers WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $image_id = $stmt->fetchColumn() ?? '';
        $dir = "./data/gallery/$image_id";

        $sql = "UPDATE offers SET name = :name, description = :description, price = :price, release_date = :release_date, mileage = :mileage, informations = :informations, equipments_list = :equipments_list WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':name' => $name, ':description' => $description, ':price' => $price, ':release_date' => $release_date, ':mileage' => $mileage, ':informations' => $informations, ':equipments_list' => $equipments_list]);

        if ($stmt->rowCount() === 1) {
            array_map('unlink', array_filter((array) glob("$dir/*")));

            for ($i = 0; $i < count($gallery); $i++) {
                $file = $gallery[$i];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $index = $i + 1;
                $filepath = "$dir/$index.$ext";
                move_uploaded_file($file, $filepath);
            }

            return true;
        }

        return true;
    }

    /**
     * La fonction supprime un enregistrement de la table "offres" de la base de données, ainsi que son fichier image et son répertoire associés.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée pour gérer les requêtes HTTP dans une application Web. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et le corps de la requête.
     * @param int id Le paramètre `id` est un nombre entier qui représente l'ID de l'offre qui doit être supprimée de la base de données.
     * 
     * @return bool une valeur booléenne. Il renvoie vrai si la suppression de l'offre avec l'ID donné est réussie, et faux sinon.
     */
    public static function delete(Request $request, int $id): bool
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT image FROM offers WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $image_id = $stmt->fetchColumn() ?? '';
        $dir = "./data/gallery/$image_id";

        $sql = "DELETE FROM offers WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            array_map('unlink', array_filter((array) glob("$dir/*")));
            rmdir($dir);

            return true;
        }
        return false;
    }

    /**
     * La fonction active ou désactive une offre dans la base de données en fonction de l'ID et de l'état fournis.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée pour gérer les requêtes HTTP dans une application Web. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et le corps de la requête.
     * @param int id Le paramètre "id" est un entier qui représente l'ID de l'offre à activer ou désactiver.
     * @param int state Le paramètre "state" est un entier qui représente la nouvelle valeur de la colonne "active" dans la table "offers". Si la valeur est 1, cela signifie que l'offre doit être activée. Si la valeur est 0, cela signifie que l'offre doit être désactivée.
     * 
     * @return bool une valeur booléenne. Elle renvoie true si la requête de mise à jour a réussi et a affecté une ligne de la base de données, et false sinon.
     */
    public static function activate(Request $request, int $id, int $state): bool
    {
        $db = $request->getAttribute('db');

        $sql = "UPDATE offers SET active = :active WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':active' => $state]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }
}
