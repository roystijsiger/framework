<?php

class FeedbackController extends Module {
    function __construct() {
        
        // Hook events to methods
        // ModulesController::HookEvent('routes.pageload', $this->ModuleName, 'RedirectIfNotAuthenticated');

        // Add routes
        Routes::Get('feedback', $this, 'NewFeedback');
        Routes::Post('feedback', $this, 'PostNewFeedback');
    }
    
    public function NewFeedback(){
        return $this->OkResult('NewFeedback');
    }
    
    public function PostNewFeedback($data){
        //dump($data);
        
       // mail("roy.stijsiger@outlook.com","onderwerp","blasdblabsd lfasd");
        //Create a new PHPMailer instance
        $mail = new PHPMailer();                          
        //Set PHPMailer to use SMTP.
        $mail->isSMTP();       
        //Enable SMTP debugging. 
        //$mail->SMTPDebug = 3;
        //Set SMTP host name                          
        $mail->Host = MAIL_SMTP_SERVER;
        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = true;                          
        //Provide username and password     
        $mail->Username = MAIL_SMTP_USERNAME;                 
        $mail->Password = MAIL_SMTP_PASSWORD;                           
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";                           
        //Set TCP port to connect to 
        $mail->Port = 587;                
        //Set who the message is to be sent from
        $mail->setFrom(MAIL_FROM, 'Feedback');
        //Set an alternative reply-to address
        $mail->addReplyTo($data['email'], $data['name']);
        //Set who the message is to be sent to
        $mail->addAddress(MAIL_SUPPORT, 'Support');
        
        $mail->isHTML(true);
         
        //Set the subject line
        $mail->Subject = 'OZ Admin feedback';
        $body = Views::Create('Mail', 'Feedback', $data, 'mail');
        
        $mail->Body = $body;
        
        //send the message, check for errors
        if (!$mail->send()) {
            exit("Mailer Error: " . $mail->ErrorInfo);
        } else { 
            return $this->OkResult('FeedbackSuccess');
        }
    }
}
