<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Libs\SlimEx as SlimEx;

class ContactController
{

    public function send(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            mb_internal_encoding("UTF-8");

            $subject = trim($data['subject']);
            if (strlen($subject) === 0) {
                return \App\Libs\SlimEx::send_error(400, "Sujet du message incorrect.", ['field' => 'subject']);
            }
            $subject = "[GVP] $subject";
            $subject_2047 = mb_encode_mimeheader($subject, "UTF-8", "B", "\n");

            $message = trim($data['message']);
            if (strlen($message) === 0) {
                return \App\Libs\SlimEx::send_error(400, "Message incorrect.", ['field' => 'message']);
            }

            $header = "";

            $name = trim($data['name']);
            if (strlen($name) === 0) {
                return \App\Libs\SlimEx::send_error(400, "Nom incorrect.", ['field' => 'name']);
            }
            $forname = trim($data['forname']);
            if (strlen($forname) === 0) {
                return \App\Libs\SlimEx::send_error(400, "Prénom incorrect.", ['field' => 'forname']);
            }
            $header = "De $forname $name\r\n";

            $email = trim($data['email']);
            if (!SlimEx::email_validator($email)) {
                return \App\Libs\SlimEx::send_error(400, "Adresse email incorrecte.", ['field' => 'email']);
            }
            $header .= "Email: $email\r\n";

            $phone = trim($data['phone']);
            if (!SlimEx::phone_validator($phone)) {
                return \App\Libs\SlimEx::send_error(400, "Numéro de téléphone français incorrect.", ['field' => 'phone']);
            }
            $header .= "Numéro de téléphone: $phone\r\n";
            $message = "$header\r\n$message";

            $ip = \App\Libs\SlimEx::get_user_ip_addr();
            $message .= "\r\n\r\n---\r\nAdresse IP de l'expéditeur: $ip";
            $message = wordwrap($message, 70, "\r\n");

            $to = $_ENV['SITENAME'] . " <" . $_ENV['EMAIL'] . ">";
            $from = "$forname $name <$email>";

            $additional_headers = 'To: ' . $to . "\r\n" .
                'From: ' . $from . "\r\n" .
                'X-Mailer: PHP/' . phpversion() . "\r\n" .
                'Content-type: text/plain; charset=UTF-8' . "\r\n";

            if (!mail($to, $subject_2047, $message, $additional_headers)) {
                return \App\Libs\SlimEx::send_error(400, "Impossible d'envoyer le message'.");
            }

            $response->getBody()->write(json_encode([
                'from'      => "$forname $name <$email>",
                'to'        => $to,
                'subject'   => $subject,
                'message'   => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            return \App\Libs\SlimEx::send_error(
                400,
                "Impossible de traiter le formulaire d'envoie du message.",
                $request->getAttribute('debug', false) ? ["debug" => $ex->getMessage()] : []
            );
        }
    }

}