<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Important : inclure l'autoload de Composer

function sendConfirmationEmail($email, $token, $base_url) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'Atlanticmarocrp@gmail.com';   
        $mail->Password = 'Atc2006@';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('Atlanticmarocrp@gmail.com', "L'équipe");
        $mail->addAddress($email);

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
