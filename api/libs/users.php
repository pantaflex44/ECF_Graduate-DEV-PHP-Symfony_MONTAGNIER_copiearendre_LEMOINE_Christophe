<?php

namespace App\Libs;

use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

/* La classe User contient des fonctions pour créer, récupérer, mettre à jour et supprimer des données utilisateur d'une base de données MySQL, ainsi que des fonctions pour définir des jetons utilisateur et activer ou désactiver des comptes utilisateur. */
class User
{

    private $request = null;

    /**
     * Cette fonction crée un nouvel objet User à partir des données MySQL et le renvoie.
     *
     * @param Request request  est une instance de la classe Request, qui est une classe du framework Laravel utilisée pour représenter une requête HTTP. Il contient des informations sur la demande telles que la méthode HTTP, les en-têtes et les paramètres.
     * @param array data  est un tableau de données qui représente les informations d'un utilisateur extraites d'une base de données MySQL. Il contient des paires clé-valeur où les clés sont les noms des attributs de l'utilisateur (par exemple, nom, e-mail, mot de passe) et les valeurs sont les valeurs correspondantes pour ces attributs.
     *
     * @return User Un objet `User` est renvoyé.
     */
    public static function fromMySQL(Request $request, array $data): User
    {
        $user = new User($request);
        foreach($data as $k => $v) {
            if ($k === 'password') continue;
            $user->{$k} = $v;
        }
        return $user;
    }

    /**
     * Cette fonction récupère un utilisateur à partir d'une base de données MySQL par ses informations d'identification de courrier électronique et de mot de passe.
     *
     * @param Request request  est une instance de la classe Request, qui fait partie du framework Slim. Il représente une requête HTTP et contient des informations sur la requête telles que la méthode HTTP, les en-têtes et le corps.
     *
     * @return soit un objet User, soit null. Si la requête renvoie une seule ligne, elle crée un objet User à l'aide de la méthode User::fromMySQL et le renvoie. Si la requête renvoie zéro ou plusieurs lignes, elle renvoie null.
     */
    public static function byCredentials(Request $request)
    {
        $db = $request->getAttribute('db');

        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $sql = "SELECT * FROM users WHERE email = :email AND password = SHA2(:password,512)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email, ':password' => $password]);

        $result = $stmt->fetchAll();
        if (count($result) !== 1) {
            return null;
        }

        return User::fromMySQL($request, $result[0]);
    }

    /**
     * Cette fonction récupère un utilisateur de la base de données par son ID et son jeton.
     *
     * @param Request request  est une instance de la classe Request, qui est utilisée pour représenter une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et le corps.
     * @param int id Un entier représentant l'ID utilisateur à rechercher dans la base de données.
     * @param string token Le paramètre "token" est une chaîne utilisée pour authentifier un utilisateur. Il est passé en argument à la fonction "byTokenId" avec l'ID de l'utilisateur. La fonction interroge ensuite la base de données pour vérifier si le jeton fourni correspond au jeton associé à l'ID de l'utilisateur. Si
     *
     * @return un objet User créé à l'aide de la méthode statique "fromMySQL" de la classe User. L'objet User est créé à partir du résultat d'une requête SELECT qui extrait un utilisateur de la base de données en fonction de l'ID et du jeton fournis. Si la requête renvoie plusieurs résultats ou aucun résultat, la fonction renvoie null.
     */
    public static function byTokenId(Request $request, int $id, string $token)
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT * FROM users WHERE id = :id AND token = :token";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':token' => $token]);

        $result = $stmt->fetchAll();
        if (count($result) !== 1) {
            return null;
        }

        return User::fromMySQL($request, $result[0]);
    }

    /**
     * Il s'agit d'une fonction constructeur qui prend un objet Request comme paramètre et l'affecte à une propriété de la classe.
     *
     * @param Request request Le paramètre `` est une instance de la classe `Illuminate\Http\Request`, qui représente une requête HTTP. Il contient des informations sur la requête telles que la méthode HTTP, les en-têtes, les paramètres de requête et le corps de la requête. Ce paramètre est injecté dans le constructeur d'une classe, ce qui signifie que le
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Cette fonction définit un jeton pour un utilisateur dans une base de données.
     *
     * @param token La valeur du jeton qui doit être définie pour l'utilisateur.
     *
     * @return bool une valeur booléenne. Il renvoie "true" si le jeton a été défini avec succès pour l'utilisateur avec l'ID spécifié, et "false" dans le cas contraire.
     */
    public function setToken($token): bool
    {
        if (!property_exists($this, 'id')) {
            return false;
        }

        $db = $this->request->getAttribute('db');

        $sql = "UPDATE users SET token = :token WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':token' => $token, ':id' => $this->{'id'}]);

        if ($stmt->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction vérifie si un email donné existe dans la table "users" d'une base de données.
     *
     * @param Request request  est une instance de la classe Request, qui est généralement utilisée dans les applications Web pour représenter une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et les paramètres.
     * @param string email L'adresse email pour vérifier si elle existe dans la table "users" de la base de données.
     *
     * @return bool Une valeur booléenne indiquant si l'email existe dans la table "users" de la base de données ou non.
     */
    public static function exists(Request $request, string $email): bool
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);

        $result = $stmt->fetchAll();
        if (count($result) !== 1) {
            return false;
        }
        return true;
    }

    /**
     * Cette fonction PHP récupère une liste d'utilisateurs d'une base de données et la renvoie sous forme de tableau.
     *
     * @param Request request  est un objet de la classe Request, qui fait probablement partie d'un framework Web tel que Laravel ou Symfony. Il contient des informations sur la requête HTTP en cours, telles que la méthode de requête, les en-têtes et les paramètres. Dans cet extrait de code spécifique, l'objet  est utilisé pour récupérer
     *
     * @return array un tableau de données utilisateur extraites de la base de données, y compris l'ID de l'utilisateur, son statut actif, son rôle, son adresse e-mail et son nom d'affichage. Si aucun résultat n'est trouvé, il renvoie un tableau vide.
     */
    public static function list(Request $request): array
    {
        $db = $request->getAttribute('db');

        $sql = "SELECT id, active, role, email, display_name FROM users";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll() ?? [];
        return $result;
    }

    /**
     * La fonction ajoute un nouvel utilisateur à une base de données avec un e-mail, un mot de passe et un nom d'affichage spécifiés.
     *
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probablement utilisé pour accéder à l'objet de connexion à la base de données () via la méthode getAttribute().
     * @param string email Chaîne représentant l'adresse e-mail de l'utilisateur ajouté à la base de données.
     * @param string password Le paramètre password est une chaîne qui représente le mot de passe de l'utilisateur. Il est passé à la fonction add en tant qu'argument et est utilisé pour insérer une version hachée du mot de passe dans la base de données.
     * @param string display_name Le nom d'affichage est une chaîne qui représente le nom qui sera affiché aux autres utilisateurs dans l'application ou le site Web. Il s'agit généralement du nom complet de l'utilisateur ou d'un nom d'utilisateur choisi par l'utilisateur.
     *
     * @return bool une valeur booléenne. Il renvoie true si l'utilisateur a été ajouté avec succès à la base de données (c'est-à-dire si le nombre de lignes affectées est de 1) et false sinon.
     */
    public static function add(Request $request, string $email, string $password, string $display_name): bool
    {
        $db = $request->getAttribute('db');

        $sql = "INSERT INTO users (active, role, email, password, display_name) VALUES (0, 'worker', :email, SHA2(:password,512), :display_name)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email, ':password' => $password, ':display_name' => $display_name]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction met à jour l'adresse e-mail et le nom d'affichage d'un utilisateur dans la base de données en fonction de son ID.
     *
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probablement utilisé pour accéder à la connexion à la base de données et aux autres dépendances nécessaires à la fonction de mise à jour.
     * @param int id ID de l'utilisateur qui doit être mis à jour dans la base de données.
     * @param string email Chaîne représentant l'adresse e-mail mise à jour de l'utilisateur.
     * @param string display_name Une chaîne représentant le nom d'affichage de l'utilisateur. C'est le nom qui sera affiché publiquement sur le profil de l'utilisateur ou dans tout autre contexte pertinent.
     *
     * @return bool une valeur booléenne. Elle renvoie true si la requête de mise à jour a réussi et a affecté une ligne, et false sinon.
     */
    public static function update(Request $request, int $id, string $email, string $display_name): bool
    {
        $db = $request->getAttribute('db');

        $sql = "UPDATE users SET email = :email, display_name = :display_name WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':email' => $email, ':display_name' => $display_name]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction supprime un utilisateur de la base de données en fonction de son ID et renvoie un booléen indiquant si la suppression a réussi.
     *
     * @param Request request  est une instance de la classe Request, qui est généralement utilisée dans les applications Web pour représenter une requête HTTP. Il contient des informations sur la demande, telles que la méthode HTTP, les en-têtes et les paramètres.
     * @param int id Le paramètre  est un entier qui représente l'identifiant unique de l'utilisateur qui doit être supprimé de la base de données.
     *
     * @return bool une valeur booléenne. Elle renvoie true si le nombre de lignes de l'instruction SQL exécutée est égal à 1, indiquant qu'une ligne a été supprimée avec succès de la base de données. Sinon, il renvoie faux.
     */
    public static function delete(Request $request, int $id): bool
    {
        $db = $request->getAttribute('db');

        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * La fonction active ou désactive un utilisateur dans une base de données en fonction de son ID.
     *
     * @param Request request Le paramètre  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probable que cette fonction fasse partie d'une application ou d'un cadre plus vaste qui utilise l'objet Request pour gérer les demandes entrantes et accéder aux données qu'elles contiennent.
     * @param int id Le paramètre id est un entier qui représente l'identifiant unique d'un utilisateur dans la base de données. Il est utilisé pour identifier l'utilisateur dont le statut actif doit être mis à jour.
     * @param int state Le paramètre "state" est un entier qui représente la nouvelle valeur de la colonne "active" dans la table "users". Si la valeur est 1, cela signifie que l'utilisateur est activé, et si la valeur est 0, cela signifie que l'utilisateur est désactivé.
     *
     * @return bool une valeur booléenne. Il renvoie "true" si la requête de mise à jour a réussi et a affecté une ligne de la base de données, et "false" dans le cas contraire.
     */
    public static function activate(Request $request, int $id, int $state): bool
    {
        $db = $request->getAttribute('db');

        $sql = "UPDATE users SET active = :active WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':active' => $state]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * Cette fonction met à jour le mot de passe d'un utilisateur dans une base de données si l'ancien mot de passe correspond au mot de passe actuel.
     *
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP en PHP. Il est probable que cette fonction fasse partie d'une application plus large qui utilise un framework tel que Slim ou Laravel, qui fournissent la classe Request dans le cadre de leurs fonctionnalités de base.
     * @param int id ID de l'utilisateur dont le mot de passe doit être mis à jour.
     * @param string old_password Le mot de passe actuel de l'utilisateur qui doit être modifié.
     * @param string new_password Le nouveau mot de passe que l'utilisateur souhaite définir. Il sera haché en utilisant SHA2 avec une longueur de 512 avant d'être stocké dans la base de données.
     *
     * @return bool une valeur booléenne. Il renvoie vrai si la mise à jour du mot de passe a réussi (c'est-à-dire que l'ancien mot de passe correspondait et que le nouveau mot de passe a été mis à jour dans la base de données), et faux si la mise à jour a échoué (c'est-à-dire que l'ancien mot de passe ne correspondait pas ou qu'il y avait une erreur dans la mise à jour de la base de données ).
     */
    public static function password(Request $request, int $id, string $old_password, string $new_password): bool
    {
        $db = $request->getAttribute('db');

        $sql = "UPDATE users SET password = SHA2(:new_password,512) WHERE id = :id AND password = SHA2(:old_password,512)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':new_password' => $new_password, ':old_password' => $old_password]);

        if ($stmt->rowCount() === 1) {
            return true;
        }
        return false;
    }

    /**
     * La fonction réinitialise le mot de passe d'un utilisateur dans une application PHP à l'aide d'un mot de passe fort généré de manière aléatoire.
     *
     * @param Request request  est une instance de la classe Request, qui est utilisée pour gérer les requêtes HTTP dans le framework Slim. Il contient des informations sur la requête en cours, telles que la méthode de requête, les en-têtes et les paramètres.
     * @param int id ID de l'utilisateur dont le mot de passe doit être réinitialisé.
     * @param string old_password L'ancien mot de passe de l'utilisateur qui doit être réinitialisé. Cependant, il n'est pas utilisé dans la fonction et semble inutile.
     * @param string new_password Le nouveau mot de passe qui sera défini pour l'utilisateur. Il est généré à l'aide de la méthode `generate_strong_password()` de la bibliothèque `Slimex`.
     *
     * @return bool un tableau à deux valeurs : une valeur booléenne indiquant si la réinitialisation du mot de passe a réussi ou non, et une valeur de chaîne contenant le nouveau mot de passe généré par la fonction.
     */
    public static function reset(Request $request, int $id): array
    {
        $db = $request->getAttribute('db');

        $new_password = \App\Libs\Slimex::generate_strong_password();

        $sql = "UPDATE users SET password = SHA2(:new_password,512) WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id, ':new_password' => $new_password]);

        if ($stmt->rowCount() === 1) {
            return [true, $new_password];
        }
        return [false, null];
    }

}
