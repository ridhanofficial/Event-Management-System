<?php
/**
 * Mailer Class
 * 
 * This class provides email functionality for the PIETECH Events Platform.
 */

// Check if vendor autoload exists - if not, we'll use our placeholder classes
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    
    public function __construct() {
        // Include email configuration
        require_once __DIR__ . '/../config/email.php';
        
        // If email is disabled, don't initialize
        if (!MAIL_ENABLED) {
            return;
        }
        
        // Create a new PHPMailer instance
        $this->mailer = new PHPMailer(true);
          try {
            // Server settings
            $this->mailer->SMTPDebug = MAIL_DEBUG;
            
            // Set secure debug output function
            if (MAIL_DEBUG > 0) {
                // Create logs directory if it doesn't exist
                $logsDir = __DIR__ . '/../logs';
                if (!file_exists($logsDir)) {
                    mkdir($logsDir, 0755, true);
                }
                
                // Use custom log function instead of displaying debug output in the browser
                $this->mailer->Debugoutput = function ($str, $level) {
                    file_put_contents(__DIR__ . '/../logs/smtp_debug.log', gmdate('Y-m-d H:i:s') . " $level: $str\n", FILE_APPEND);
                };
            }
            
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = MAIL_AUTH;
            $this->mailer->Username = MAIL_USERNAME;
            $this->mailer->Password = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
            $this->mailer->Port = MAIL_PORT;
            
            // Common settings for Gmail
            if (strpos(MAIL_HOST, 'gmail') !== false) {
                $this->mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
            }
            
            // Default sender
            $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
        }
    }
    
    /**
     * Send an email
     * 
     * @param string $to Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $altBody Plain text alternative (optional)
     * @return bool Success status
     */
    public function send($to, $toName, $subject, $body, $altBody = '') {
        // If email is disabled, return success without sending
        if (!MAIL_ENABLED || !isset($this->mailer)) {
            return true;
        }
        
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            
            // Add recipient
            $this->mailer->addAddress($to, $toName);
            
            // Set content type
            $this->mailer->isHTML(true);
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ? $altBody : strip_tags($body);
            
            // For debugging: log that we are about to send
            if (MAIL_DEBUG > 0) {
                error_log("Attempting to send email to: $to with subject: $subject");
            }
            
            // Send the email
            $result = $this->mailer->send();
            
            // For debugging: log the result
            if (MAIL_DEBUG > 0) {
                error_log("Email sent result: " . ($result ? "Success" : "Failed"));
            }
            
            return $result;
        } catch (Exception $e) {
            // Log error
            error_log("Email sending failed: " . $this->mailer->ErrorInfo . " - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send event registration confirmation email
     * 
     * @param array $userData User data (name, email)
     * @param array $eventData Event data (title, venue, room_no, date, time)
     * @return bool Success status
     */
    public function sendEventRegistrationConfirmation($userData, $eventData) {
        $subject = "Registration Confirmation: " . $eventData['title'];
        
        // Format date and time
        $formattedDate = date('F j, Y', strtotime($eventData['date']));
        $formattedTime = date('g:i A', strtotime($eventData['time']));
          // Generate a unique confirmation number
        $confirmationNumber = strtoupper(substr(md5(uniqid($userData['email'] . time())), 0, 8));
        
        // Create email body
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Event Registration Confirmation</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
                
                body { 
                    font-family: 'Roboto', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f5f5f5; 
                }
                
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                }
                
                .header { 
                    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); 
                    color: #fff; 
                    padding: 20px; 
                    text-align: center; 
                }
                
                .logo {
                    max-width: 150px;
                    margin-bottom: 10px;
                }
                
                .content { 
                    padding: 30px; 
                    background-color: #ffffff; 
                }
                
                .confirmation-box {
                    background-color: #ebf8ff;
                    border: 1px solid #B3E5FC;
                    border-radius: 6px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                
                .confirmation-number {
                    font-size: 24px;
                    font-weight: bold;
                    color:rgb(29, 108, 227);
                    letter-spacing: 2px;
                }
                
                .event-details { 
                    background-color: #fff; 
                    padding: 20px; 
                    border-radius: 6px;
                    border-left: 4px solid #4a5568; 
                    margin: 20px 0; 
                    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                }
                  .event-details h3 {
                    margin-top: 0;
                    color: #2d3748;
                }
                
                .footer {
                    text-align: center; 
                    margin-top: 20px; 
                    padding: 20px;
                    background-color: #f8f9fa;
                    font-size: 12px; 
                    color: #6c757d; 
                }
                
                h1 { 
                    color: #2d3748; 
                    margin-top: 0;
                }
                
                .button {
                    display: inline-block;
                    background-color: #4A5568;
                    color: #fff;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                    margin: 15px 0;
                }
                
                .button:hover {
                    background-color: #2d3748;
                }
                
                .divider {
                    height: 1px;
                    background-color: #e2e8f0;
                    margin: 25px 0;
                }
                
                .contact-info {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 6px;
                    margin-top: 20px;
                }
                
                @media only screen and (max-width: 600px) {
                    .container {
                        width: 100% !important;
                        border-radius: 0;
                    }
                    
                    .content {
                        padding: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='" . APP_URL . "/images/logo.png' alt='PIETECH Logo' class='logo'>
                    <h2>PIETECH Events Platform</h2>
                </div>
                
                <div class='content'>
                    <h1>Registration Confirmation</h1>
                    <p>Dear {$userData['name']},</p>
                    <p>Thank you for registering for the following event. We're excited to have you join us!</p>
                      <div class='confirmation-box'>
                        <p>Your confirmation number:</p>
                        <div class='confirmation-number'>{$confirmationNumber}</div>
                        <p><small>Please keep this number for your records</small></p>
                    </div>
                    
                    <div class='event-details'>
                        <h3>{$eventData['title']}</h3>
                        <p><strong>Venue:</strong> {$eventData['venue']}</p>
                        <p><strong>Room Number:</strong> {$eventData['room_no']}</p>
                        <p><strong>Date:</strong> {$formattedDate}</p>
                        <p><strong>Time:</strong> {$formattedTime}</p>
                    </div>
                    <div class='divider'></div>
                    
                    <p>Please remember to arrive at least 15 minutes before the event starts. If you need to cancel your registration, you can do so by visiting your account dashboard.</p>                      <p style='text-align: center;'>
                        <a href='" . APP_URL . "/my_registrations.php' class='button' style='background-color: #4A5568; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>View My Registrations</a>
                    </p>
                    
                    <div class='contact-info'>
                        <p><strong>Questions?</strong> If you have any questions or need assistance, please don't hesitate to contact us at <a href='mailto:support@pietechevents.com'>support@pietechevents.com</a>.</p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " PIETECH Events Platform. All rights reserved.</p>
                    <p><small>This email was sent to {$userData['email']}. If you did not register for this event, please disregard this email.</small></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send the email
        return $this->send($userData['email'], $userData['name'], $subject, $body);
    }
    
    /**
     * Send account verification email
     * 
     * @param string $email User email
     * @param string $name User name
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendVerificationEmail($email, $name, $token) {
        $subject = "Verify Your PIETECH Events Account";
        
        // Create verification link
        $verificationLink = APP_URL . "/verify.php?token=" . $token;
        
        // Create email body
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Account Verification</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
                
                body { 
                    font-family: 'Roboto', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f5f5f5; 
                }
                
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                }
                
                .header { 
                    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); 
                    color: #fff; 
                    padding: 20px; 
                    text-align: center; 
                }
                
                .logo {
                    max-width: 150px;
                    margin-bottom: 10px;
                }
                
                .content { 
                    padding: 30px; 
                    background-color: #ffffff; 
                }
                
                .verification-box {
                    background-color: #f0fff4;
                    border: 1px solid #c6f6d5;
                    border-radius: 6px;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: center;
                }
                
                .footer { 
                    text-align: center; 
                    margin-top: 20px; 
                    padding: 20px;
                    background-color: #f8f9fa;
                    font-size: 12px; 
                    color: #6c757d; 
                }
                
                h1 { 
                    color: #2d3748; 
                    margin-top: 0;
                }
                
                .button {
                    display: inline-block;
                    background-color: #4A5568;
                    color: #fff !important;
                    padding: 14px 28px;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                    margin: 15px 0;
                    font-size: 16px;
                    border: none;
                    cursor: pointer;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                
                .button:hover {
                    background-color: #4A5568;
                }
                
                .divider {
                    height: 1px;
                    background-color: #e2e8f0;
                    margin: 25px 0;
                }
                
                .verification-link {
                    word-break: break-all;
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 4px;
                    border: 1px solid #e2e8f0;
                    font-family: monospace;
                    margin: 15px 0;
                    font-size: 14px;
                }
                
                .important-note {
                    background-color: #fff8f1;
                    border-left: 4px solid #ed8936;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 0 4px 4px 0;
                }
                
                @media only screen and (max-width: 600px) {
                    .container {
                        width: 100% !important;
                        border-radius: 0;
                    }
                    
                    .content {
                        padding: 20px;
                    }
                    
                    .button {
                        display: block;
                        width: 100%;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='" . APP_URL . "/images/logo.png' alt='PIETECH Logo' class='logo'>
                    <h2>PIETECH Events Platform</h2>
                </div>
                
                <div class='content'>
                    <h1>Verify Your Email Address</h1>
                    <p>Dear {$name},</p>
                    <p>Thank you for registering with the PIETECH Events Platform. We're excited to have you join our community!</p>
                    
                    <div class='verification-box'>
                        <p>To complete your registration and access all features, please verify your email address by clicking the button below:</p>
                        
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                        </p>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
                    <div class='verification-link'>{$verificationLink}</div>
                    
                    <div class='important-note'>
                        <p><strong>Important:</strong> This verification link will expire in 24 hours for security reasons. Please verify your email as soon as possible.</p>
                    </div>
                    
                    <p>After verification, you'll be able to:</p>
                    <ul>
                        <li>Register for upcoming events</li>
                        <li>Manage your event registrations</li>
                        <li>Receive updates about events you're interested in</li>
                    </ul>
                    
                    <p>If you did not create an account with us, please disregard this email.</p>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " PIETECH Events Platform. All rights reserved.</p>
                    <p><small>This email was sent to {$email}. Please do not reply to this email.</small></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send the email
        return $this->send($email, $name, $subject, $body);
    }
}