<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // chemin vers autoload Composer

function sendConfirmationEmail($email, $token, $base_url) {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'ton.email@gmail.com';   // Ton email Gmail
        $mail->Password = 'ton_mot_de_passe_app';  // Mot de passe d’application Gmail (voir note)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('ton.email@gmail.com', "L'équipe");
        $mail->addAddress($email);

        // Contenu de l’email
        $mail->isHTML(false);
        $mail->Subject = 'Confirmez votre inscription';
        $confirmation_link = $base_url . "confirm.php?token=" . urlencode($token);
        $mail->Body = "Bonjour,\n\nMerci de confirmer votre inscription en cliquant sur ce lien:\n$confirmation_link\n\nCordialement,\nL'équipe";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur mail: " . $mail->ErrorInfo);
        return false;
    }
}
