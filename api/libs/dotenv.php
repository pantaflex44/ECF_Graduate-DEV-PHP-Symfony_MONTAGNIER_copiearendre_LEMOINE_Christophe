<?php

namespace App\Libs;

/* La classe DotEnvEnvironment charge les variables d'environnement à partir d'un fichier .env et les définit dans les superglobales  et . */
class DotEnvEnvironment
{
    /**
     * Cette fonction charge les variables d'environnement à partir d'un fichier et les définit dans l'environnement PHP.
     *
     * @param string path Le chemin d'accès au répertoire où se trouve le fichier .env.
     */
    public function load(string $path): void
    {
        $lines = file($path . '/.env');

        foreach ($lines as $line) {
            try {
                $e = explode('=', $line, 2);

                $key = trim($e[0]);
                if ($key == '') {
                    continue;
                }

                $value = trim(count($e) > 1 ? $e[1] : '');

                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            } catch (\Exception $ex) {
            }
        }
    }
}
