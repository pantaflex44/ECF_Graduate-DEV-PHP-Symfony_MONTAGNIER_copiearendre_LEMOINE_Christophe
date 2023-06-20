<?php

namespace App\Libs;

use DateInterval;
use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

/* La classe Comments contient deux méthodes statiques pour récupérer des listes de commentaires à partir d'une base de données, une pour tous les commentaires et une pour les commentaires approuvés. */
class Comments
{

    /**
     * Cette fonction PHP récupère tous les commentaires d'une base de données et les renvoie sous forme de tableau.
     * 
     * @param Request request  est un objet de la classe Request, qui provient probablement d'un framework PHP tel que Laravel ou Symfony. Il contient des informations sur la requête HTTP en cours, telles que la méthode de requête, les en-têtes et tous les paramètres ou données envoyés dans la requête. Dans cette fonction spécifique,  est utilisé
     * 
     * @return array un tableau de toutes les lignes de la table "commentaires" de la base de données. S'il n'y a pas de lignes, il renverra un tableau vide.
     */
    public static function list(Request $request): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT * FROM comments";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result ?? [];
    }

    /**
     * Cette fonction PHP récupère une liste de commentaires approuvés à partir d'une base de données.
     * 
     * @param Request request  est un objet de la classe Request, qui est utilisé pour gérer les requêtes HTTP en PHP. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et les paramètres. Dans ce code,  est utilisé pour accéder à l'objet de connexion à la base de données () qui a été transmis en tant que
     * 
     * @return array un tableau de tous les commentaires de la base de données où la colonne "approuvé" est définie sur 1. S'il n'y a pas de résultats, il renvoie un tableau vide.
     */
    public static function approved_list(Request $request): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT id, name, comment, rating FROM comments WHERE approved = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result ?? [];
    }

    /**
     * Cette fonction PHP renvoie l'intervalle de temps entre le dernier commentaire posté par une adresse IP donnée et l'heure actuelle.
     * 
     * @param Request request  est une instance de la classe Request, qui est généralement utilisée dans les applications Web pour représenter une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et les paramètres de requête.
     * @param string ip L'adresse IP de l'utilisateur dont le dernier intervalle publié est en cours de calcul.
     * 
     * @return DateInterval Un objet `DateInterval` est renvoyé.
     */
    public static function last_posted_interval(Request $request, string $ip): DateInterval|null
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT dt FROM comments WHERE ip = :ip ORDER BY dt DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':ip' => $ip]);

        $result = $stmt->fetchAll() ?? [];
        if (count($result) === 0) return null;

        $last = new \DateTime($result[0]['dt']);
        $now = new \DateTime();
        return $last->diff($now);
    }

    /**
     * La fonction ajoute un nouveau service avec un nom, un commentaire et une évaluation à une base de données à l'aide d'une instruction SQL préparée.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probablement utilisé pour accéder à l'objet de connexion à la base de données () dans cette fonction.
     * @param string name Chaîne représentant le nom d'un service à ajouter à une table de base de données.
     * @param string comment Chaîne représentant le commentaire qu'un utilisateur souhaite ajouter à un service.
     * @param float rating Le paramètre d'évaluation est un type de données flottant, qui représente la valeur numérique de l'évaluation donnée à un service. Il est utilisé dans la requête SQL pour insérer la valeur d'évaluation dans la table de la base de données.
     * 
     * @return bool une valeur booléenne. Elle renvoie true si l'insertion des données dans la base de données a réussi (c'est-à-dire si le nombre de lignes affectées est égal à 1), et false sinon.
     */
    public static function add(Request $request, string $name, string $comment, float $rating, string $ip): bool
    {
        $db = $request->getAttribute('db');

        $sql = "INSERT INTO comments (name, comment, rating, ip) VALUES (:name, :comment, :rating, :ip)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name, ':comment' => $comment, ':rating' => $rating, ':ip' => $ip]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction PHP supprime un commentaire d'une base de données en fonction de son ID.
     * 
     * @param Request request  est une instance de la classe Request, qui est généralement utilisée dans les frameworks PHP comme Slim ou Laravel pour gérer les requêtes HTTP et fournir un accès aux données de la requête telles que les en-têtes, les paramètres et le corps de la requête. Dans ce cas, il est utilisé pour accéder à l'objet de connexion à la base de données () qui a été
     * @param int id Le paramètre id est un entier qui représente l'identifiant unique du commentaire qui doit être supprimé de la base de données.
     * 
     * @return bool Une valeur booléenne indiquant si la suppression a réussi ou non. Si le nombre de lignes affectées est 1, elle renvoie true, sinon elle renvoie false.
     */
    public static function delete(Request $request, int $id): bool
    {
        $db = $request->getAttribute('db');

        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

}