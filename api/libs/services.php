<?php

namespace App\Libs;

use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

/* La classe Service a une méthode statique qui récupère tous les enregistrements d'une table "services" dans une base de données. */

class Service
{

    /**
     * Cette fonction PHP récupère toutes les lignes d'une table "services" dans une base de données et les renvoie sous forme de tableau.
     * 
     * @param Request request  est un objet de la classe Request, qui provient probablement d'un framework PHP tel que Laravel ou Symfony. Il contient des informations sur la requête HTTP en cours, telles que la méthode de requête, les en-têtes et les paramètres. Dans cet extrait de code spécifique, l'objet  est utilisé pour récupérer une base de données
     * 
     * @return array un tableau de toutes les lignes de la table "services" de la base de données. S'il n'y a pas de lignes, il renverra un tableau vide.
     */
    public static function list(Request $request): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT * FROM services";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result ?? [];
    }

    /**
     * Cette fonction PHP ajoute un nouveau service à une base de données avec un nom, une description et une image.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probablement utilisé pour accéder à la connexion à la base de données et aux autres dépendances nécessaires au bon fonctionnement de la fonction.
     * @param string name Chaîne représentant le nom du service ajouté à la base de données.
     * @param string description Une variable de chaîne qui contient la description d'un service.
     * @param string dataUri  est un paramètre de chaîne qui représente les données d'image au format base64. Il est utilisé pour stocker l'image dans la base de données sous forme de blob.
     * 
     * @return bool Une valeur booléenne est renvoyée. Si le nombre de lignes de l'instruction SQL exécutée est 1, true est renvoyé. Sinon, false est renvoyé.
     */
    public static function add(Request $request, string $name, float $amount, string $description, string $dataUri): bool
    {
        $db = $request->getAttribute('db');

        $sql = "INSERT INTO services (name, amount, description, image) VALUES (:name, :amount, :description, :image)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name, ':amount' => $amount, ':description' => $description, ':image' => $dataUri]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction met à jour le nom, la description et l'image d'un utilisateur dans une base de données en fonction de son ID.
     * 
     * @param Request request Une instance de la classe Request, qui provient probablement d'un framework Web tel que Laravel ou Symfony. Il contient des informations sur la requête HTTP en cours, telles que les en-têtes, les paramètres de requête et le corps de la requête.
     * @param int id ID de l'utilisateur qui doit être mis à jour dans la base de données.
     * @param string name Une chaîne représentant le nom mis à jour de l'utilisateur.
     * @param string description Une variable de chaîne qui contient la description mise à jour d'un utilisateur dans une base de données.
     * @param string dataUri Le paramètre dataUri est une chaîne qui représente les données d'image dans un format URI. Il est utilisé pour mettre à jour le champ image dans la table des utilisateurs d'une base de données.
     * 
     * @return bool une valeur booléenne. Elle renvoie true si la requête de mise à jour a réussi et a affecté une ligne, et false sinon.
     */
    public static function update(Request $request, int $id, string $name, float $amount, string $description, string $dataUri): bool
    {
        $db = $request->getAttribute('db');

        $sql = "UPDATE services SET name = :name, amount = :amount, description = :description, image = :image WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':name' => $name, ':amount' => $amount, ':description' => $description, ':image' => $dataUri]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction PHP supprime une ligne de la table "services" dans une base de données en fonction de l'ID fourni.
     * 
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et les paramètres.
     * @param int id Le paramètre  est un entier qui représente l'ID du service qui doit être supprimé de la base de données.
     * 
     * @return bool Une valeur booléenne indiquant si la suppression a réussi ou non. Si le nombre de lignes affectées est 1, elle renvoie true, sinon elle renvoie false.
     */
    public static function delete(Request $request, int $id): bool
    {
        $db = $request->getAttribute('db');

        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }
}
