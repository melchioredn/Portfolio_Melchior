<?php
/**
 * PHP Email Form Library avec PHPMailer
 * Compatible BootstrapMade + SMTP Gmail
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclure PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class PHP_Email_Form {

  public $ajax = false;
  public $to = '';
  public $from_name = '';
  public $from_email = '';
  public $subject = '';
  public $cc = [];
  public $bcc = [];
  public $smtp = null; // array('host'=>'', 'username'=>'', 'password'=>'', 'port'=>587)
  public $messages = [];

  public function add_message($message, $label = '', $priority = 0) {
    $this->messages[] = ['message' => $message, 'label' => $label, 'priority' => $priority];
  }

  public function send() {
    if (empty($this->to) || empty($this->from_email) || empty($this->subject) || empty($this->messages)) {
      return 'Formulaire incomplet.';
    }

    // Trier par priorité
    usort($this->messages, fn($a, $b) => $b['priority'] <=> $a['priority']);
    $body = '';
    foreach ($this->messages as $m) {
      $body .= ($m['label'] ? "{$m['label']}: " : '') . $m['message'] . "\n";
    }

    try {
      $mail = new PHPMailer(true);

      if ($this->smtp && !empty($this->smtp['host'])) {
        // Config SMTP
        $mail->isSMTP();
        $mail->Host       = $this->smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->smtp['username'];
        $mail->Password   = $this->smtp['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $this->smtp['port'];
      }

      // Expéditeur / Destinataire
      $mail->setFrom($this->from_email, $this->from_name);
      $mail->addAddress($this->to);

      foreach ($this->cc as $email) { $mail->addCC($email); }
      foreach ($this->bcc as $email) { $mail->addBCC($email); }

      // Sujet & contenu
      $mail->Subject = $this->subject;
      $mail->Body    = $body;

      $mail->send();
      return 'OK';

    } catch (Exception $e) {
      return "Erreur : " . $mail->ErrorInfo;
    }
  }
}
?>
