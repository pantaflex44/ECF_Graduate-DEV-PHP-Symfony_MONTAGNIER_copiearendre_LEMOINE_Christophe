<?php

namespace App\Libs;

use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

/* La classe OpeningHours fournit des méthodes pour répertorier, vérifier et ajouter des heures d'ouverture à une base de données. */

class OpeningHours
{

    /**
     * Cette fonction PHP récupère les heures d'ouverture d'une base de données et les formate dans un tableau regroupé par jour.
     * 
     * @param Request request  est une instance de la classe Request, qui fait probablement partie d'un framework Web tel que Laravel ou Symfony. Il est utilisé pour récupérer des informations sur la requête HTTP entrante, telles que les paramètres de requête, les en-têtes et le corps de la requête. Dans cette fonction spécifique, il est utilisé pour récupérer une base de données
     * 
     * @return array un tableau des heures d'ouverture pour chaque jour de la semaine, triées par jour et heure de début. Chaque jour a un tableau d'heures d'ouverture, chaque heure d'ouverture étant représentée sous la forme d'un tableau avec des touches « ouvrir » et « fermer », indiquant les heures d'ouverture et de fermeture pour cette période.
     */
    public static function list(Request $request): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT * FROM opening_hours ORDER BY day ASC, start ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll() ?? [];

        $hours = [
            ['day' => 'Dimanche', 'dayOfWeek' => 0, 'hours' => []],
            ['day' => 'Lundi', 'dayOfWeek' => 1, 'hours' => []],
            ['day' => 'Mardi', 'dayOfWeek' => 2, 'hours' => []],
            ['day' => 'Mercredi', 'dayOfWeek' => 3, 'hours' => []],
            ['day' => 'Jeudi', 'dayOfWeek' => 4, 'hours' => []],
            ['day' => 'Vendredi', 'dayOfWeek' => 5, 'hours' => []],
            ['day' => 'Samedi', 'dayOfWeek' => 6, 'hours' => []],
        ];
        foreach ($result as $it) {
            $open_minutes = substr($it['start'], -2);
            $open_hours = substr($it['start'], 0, strlen($it['start']) - 2);
            $close_minutes = substr($it['end'], -2);
            $close_hours = substr($it['end'], 0, strlen($it['end']) - 2);
            $hours[$it['day']]['hours'][] = ['id' => $it['id'], 'open' => sprintf("%sh%s", $open_hours, $open_minutes), 'close' => sprintf("%sh%s", $close_hours, $close_minutes)];
        }

        return $hours;
    }

    /**
     * La fonction vérifie si une heure de début donnée existe dans les heures d'ouverture pour un jour spécifique de la semaine.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il contient des informations sur la requête, telles que la méthode de requête, les en-têtes et les paramètres.
     * @param int dayOfWeek Un entier représentant le jour de la semaine (0 pour dimanche, 1 pour lundi, etc.).
     * @param int open_hours L'heure à laquelle l'entreprise ouvre.
     * @param int open_minutes Le paramètre "open_minutes" est un entier représentant la composante minutes de l'heure d'ouverture d'un commerce ou d'un établissement. Il est utilisé dans la fonction pour construire une chaîne d'heure de début au format "HHMM" (heures et minutes) pour interroger la base de données sur les heures d'ouverture d'un jour spécifique du
     * 
     * @return bool Une valeur booléenne est renvoyée.
     */
    public static function start_exists(Request $request, int $dayOfWeek, int $open_hours, int $open_minutes): bool
    {
        $db = $request->getAttribute('db');

        $start = str_pad($open_hours, 2, '0', STR_PAD_LEFT) . str_pad($open_minutes, 2, '0', STR_PAD_LEFT);

        $sql = "SELECT * FROM opening_hours WHERE day = :day AND :start BETWEEN start AND end";
        $stmt = $db->prepare($sql);
        $stmt->execute([':day' => $dayOfWeek, ':start' => $start]);

        $result = $stmt->fetchAll();
        if (count($result) !== 1) {
            return false;
        }
        return true;
    }

    /**
     * La fonction vérifie si une heure de fermeture donnée existe pour un jour spécifique de la semaine dans une table de base de données des heures d'ouverture.
     * 
     * @param Request request Le paramètre  est une instance de la classe Request, qui est utilisée pour récupérer des informations sur la requête HTTP en cours, telles que la connexion à la base de données et tous les paramètres ou données envoyés dans la requête.
     * @param int dayOfWeek Un entier représentant le jour de la semaine (0 pour dimanche, 1 pour lundi, etc.).
     * @param int close_hours L'heure à laquelle l'établissement ferme.
     * @param int close_minutes Le paramètre close_minutes représente la composante minutes de l'heure de fermeture d'une entreprise ou d'un établissement. Il est utilisé dans la fonction pour vérifier si l'heure de fermeture existe dans la base de données pour un jour donné de la semaine.
     * 
     * @return bool Une valeur booléenne est renvoyée.
     */
    public static function end_exists(Request $request, int $dayOfWeek, int $close_hours, int $close_minutes): bool
    {
        $db = $request->getAttribute('db');

        $end = str_pad($close_hours, 2, '0', STR_PAD_LEFT) . str_pad($close_minutes, 2, '0', STR_PAD_LEFT);

        $sql = "SELECT * FROM opening_hours WHERE day = :day AND :end BETWEEN start AND end";
        $stmt = $db->prepare($sql);
        $stmt->execute([':day' => $dayOfWeek, ':end' => $end]);

        $result = $stmt->fetchAll();
        if (count($result) !== 1) {
            return false;
        }
        return true;
    }

    /**
     * La fonction ajoute les heures d'ouverture à une base de données pour un jour spécifique de la semaine.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour récupérer des informations sur la requête HTTP en cours, telles que la méthode de requête, les en-têtes et les paramètres.
     * @param int dayOfWeek Un entier représentant le jour de la semaine (1 pour lundi, 2 pour mardi, etc.).
     * @param int open_hours L'heure d'ouverture d'un commerce un jour spécifique de la semaine, représentée sous la forme d'un nombre entier. Par exemple, 9 pour 9h.
     * @param int open_minutes Le paramètre open_minutes est un nombre entier représentant la composante minutes de l'heure d'ouverture d'un commerce un jour spécifique de la semaine.
     * @param int close_hours Le paramètre close_hours est un entier représentant l'heure à laquelle le commerce ou l'établissement ferme.
     * @param int close_minutes Le paramètre "close_minutes" est un nombre entier représentant la composante minutes de l'heure de fermeture d'un commerce un jour spécifique de la semaine.
     * 
     * @return bool une valeur booléenne - "true" si l'insertion a réussi (c'est-à-dire si le nombre de lignes affectées est de 1) et "false" sinon.
     */
    public static function add(Request $request, int $dayOfWeek, int $open_hours, int $open_minutes, int $close_hours, int $close_minutes): bool
    {
        $db = $request->getAttribute('db');

        $start = str_pad($open_hours, 2, '0', STR_PAD_LEFT) . str_pad($open_minutes, 2, '0', STR_PAD_LEFT);
        $end = str_pad($close_hours, 2, '0', STR_PAD_LEFT) . str_pad($close_minutes, 2, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO opening_hours (day, start, end) VALUES (:day, :start, :end)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':day' => $dayOfWeek, ':start' => $start, ':end' => $end]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction met à jour les heures d'ouverture d'un jour spécifique dans une table de base de données.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour récupérer des informations sur la requête HTTP en cours.
     * @param int id ID de l'enregistrement des heures d'ouverture à mettre à jour dans la base de données.
     * @param int dayOfWeek Un entier représentant le jour de la semaine (0 pour dimanche, 1 pour lundi, etc.).
     * @param int open_hours L'heure d'ouverture d'un commerce un jour précis de la semaine.
     * @param int open_minutes Le paramètre open_minutes est un nombre entier représentant la composante minutes de l'heure d'ouverture d'un commerce un jour spécifique de la semaine.
     * @param int close_hours Le paramètre close_hours est un entier représentant l'heure à laquelle une entreprise ou un établissement ferme.
     * @param int close_minutes Le paramètre close_minutes est un nombre entier représentant la composante minutes de l'heure de fermeture d'une entreprise un jour spécifique de la semaine.
     * 
     * @return bool Une valeur booléenne indiquant si la mise à jour a réussi ou non.
     */
    public static function update(Request $request, int $id,  int $dayOfWeek, int $open_hours, int $open_minutes, int $close_hours, int $close_minutes): bool
    {
        $db = $request->getAttribute('db');

        $start = str_pad($open_hours, 2, '0', STR_PAD_LEFT) . str_pad($open_minutes, 2, '0', STR_PAD_LEFT);
        $end = str_pad($close_hours, 2, '0', STR_PAD_LEFT) . str_pad($close_minutes, 2, '0', STR_PAD_LEFT);

        $sql = "UPDATE opening_hours SET day = :day, start = :start, end = :end WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':day' => $dayOfWeek, ':start' => $start, ':end' => $end]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction PHP supprime une ligne d'une table de base de données en fonction de l'ID fourni.
     * 
     * @param Request request  est une instance de la classe Request, qui est généralement utilisée dans les frameworks PHP comme Slim ou Laravel pour gérer les requêtes HTTP et fournir un accès aux données de la requête telles que les en-têtes, les paramètres et le corps de la requête. Dans ce cas, il est utilisé pour accéder à l'objet de connexion à la base de données () qui a été
     * @param int id Le paramètre  est un entier qui représente l'identifiant unique de l'enregistrement des heures d'ouverture qui doit être supprimé de la base de données.
     * 
     * @return bool Une valeur booléenne indiquant si la suppression a réussi ou non. Si le nombre de lignes affectées est 1, elle renvoie true, sinon elle renvoie false.
     */
    public static function delete(Request $request, int $id): bool
    {
        $db = $request->getAttribute('db');

        $sql = "DELETE FROM opening_hours WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }
}
