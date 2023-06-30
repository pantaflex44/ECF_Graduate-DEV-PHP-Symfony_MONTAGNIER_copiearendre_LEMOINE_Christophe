<?php

namespace App\Libs;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class Offers
{

    /* Le code ci-dessus définit un tableau qui contient des informations sur divers filtres pouvant être appliqués à un ensemble de données. Chaque filtre est identifié par une clé (par exemple 'name', 'description', 'doors', etc.) et a une valeur correspondante qui est un tableau avec deux clés : 'filter' et 'args'. */
    public static array $all_filters = [
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
        'doors'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Nombre de portes"],
        'color'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Couleur"],
        'brand'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Marque"],
        'type'              => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Type de carrosserie"],
        'fuel'              => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Carburant"],
        'model'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Modèle"],
        'sites'             => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Nombre de places"],
        'gearbox'           => ['limits' => 'filters_limits_multi', 'column' => 'informations', 'label' => "Boite de vitesse"],

        'din'               => ['limits' => 'filters_limits_range', 'column' => 'informations', 'label' => "Puissance DIN"],
        'fiscal'            => ['limits' => 'filters_limits_range', 'column' => 'informations', 'label' => "Puissance fiscale"],
        'mileage'           => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Kilométrage"],
        'price'             => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Prix"],
        'release_date'      => ['limits' => 'filters_limits_range', 'column' => null, 'label' => "Date de mise en service"],
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
    public static function filters_to_mysql(array $filters): array
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
    public static function list(Request $request, bool $active_only, array $filters, int $page = 1, int $per_page = 20): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT COUNT(*) FROM offers";
        if ($active_only) $sql .= " WHERE active = 1";
        $where = Offers::filters_to_mysql($filters);
        if (count($where['sql']) > 0) {
            $wf = trim(implode(' OR ', array_filter($where['sql'], function ($v, $k) {
                return !is_null($v);
            }, ARRAY_FILTER_USE_BOTH)));
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
            $ret = array_merge($ret, ['query' => ['sql' => $sql, 'values' => $where['values']]]);
        }

        foreach ($result as $offer) {
            $offer['equipments_list'] = json_decode($offer['equipments_list']);
            $offer['informations'] = json_decode($offer['informations']);
            $offer['price'] = floatval($offer['price']);
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

        return ['key' => $key, 'options' => $options];
    }

    /**
     * La fonction génère un élément de formulaire HTML pour un filtre à sélection multiple avec options.
     * 
     * @param string name Le paramètre name est une chaîne qui représente le nom du filtre. Il est utilisé pour générer les attributs HTML id et name pour l'élément input et l'élément datalist.
     * @param array limits Le paramètre "limits" est un tableau qui contient les options du filtre. Il devrait avoir une clé appelée "options" qui contient un tableau de valeurs qui seront utilisées pour remplir la liste de données dans le formulaire.
     * 
     * @return array Un tableau est renvoyé avec trois paires clé-valeur. Les clés sont 'form_id', 'form_name' et 'form_html5'. Les valeurs pour 'form_id' et 'form_name' sont toutes deux "filter_", où  est le paramètre d'entrée. La valeur de 'form_html5' est le code HTML d'un élément de formulaire, qui comprend un champ de saisie et une donnée
     */
    public static function filters_form_multi(string $name, array $limits): array
    {
        $form = '';

        if (array_key_exists('options', $limits)) {
            $form .= '<input type="text" list="' . $name . '_list" name="filter_' . $name . '" id="filter_' . $name . '" />' . "\n";
            $form .= '<datalist id="' . $name . '_list">' . "\n";
            foreach ($limits['options'] as $option) {
                $form .= '<option value="' . $option . '">' . $option . '</option>' . "\n";
            }
            $form .= '</datalist>';
        }

        return ['form_id' => "filter_$name", 'form_name' => "filter_$name", 'form_html5' => $form];
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

        return ['key' => $key, 'min' => $min, 'max' => $max];
    }

    /**
     * La fonction génère un élément de formulaire HTML pour une entrée de plage avec des valeurs minimales et maximales spécifiées.
     * 
     * @param string name Le paramètre name est une chaîne qui représente le nom du filtre de plage. Il est utilisé pour générer l'attribut de nom de l'élément HTML et pour créer des identifiants uniques pour l'élément de filtre.
     * @param array limits Le paramètre `limits` est un tableau qui contient les valeurs minimales et maximales pour l'entrée de plage. Il doit avoir la structure suivante :
     * 
     * @return array Un tableau est renvoyé avec les clés et les valeurs suivantes :
     */
    public static function filters_form_range(string $name, array $limits): array
    {
        $form = '';

        if (array_key_exists('min', $limits) && array_key_exists('max', $limits)) {
            $form .= '<input type="range" name="filter_' . $name . '" id="filter_' . $name . '" min="' . $limits['min'] . '" max="' . $limits['max'] . '" />';
        }

        return ['form_id' => "filter_$name", 'form_name' => "filter_$name", 'form_html5' => $form];
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
                    $filters[$name] = call_user_func_array(__CLASS__ . "::" . $item['limits'], [$request, $name, $item['column']]);
                }
            } catch (Exception $ex) {
            }
        }

        return $filters;
    }

    /**
     * La fonction "filters_limits_form" génère un tableau de filtres et leurs limites et formes correspondantes en fonction de la requête donnée.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est généralement utilisée pour récupérer des informations à partir de la requête HTTP adressée au serveur. Il contient des données telles que la méthode de requête, les en-têtes, les paramètres de requête et les données de formulaire. Dans cette fonction, le paramètre  est utilisé pour le passer à
     * 
     * @return array un ensemble de filtres.
     */
    public static function filters_limits_form(Request $request): array
    {
        $filters = [];

        foreach (Offers::$all_filters_limits as $name => $item) {
            try {
                $rm = new \ReflectionMethod(__CLASS__, $item['limits']);
                if (!is_null($rm) && $rm->isStatic()) {
                    $limits = call_user_func_array(__CLASS__ . "::" . $item['limits'], [$request, $name, $item['column']]);

                    $frm_caller = str_replace('limits', 'form', $item['limits']);
                    $rm = new \ReflectionMethod(__CLASS__, $frm_caller);
                    if (!is_null($rm) && $rm->isStatic()) {
                        $form = call_user_func_array(__CLASS__ . "::" . $frm_caller, [$name, $limits]);
                        $filters[$name] = array_merge($limits, $form, ['form_label' => $item['label']]);
                    }
                }
            } catch (Exception $ex) {
            }
        }

        return $filters;
    }
}
