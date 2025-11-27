<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Email;

class PhpMailTransport extends AbstractTransport
{
    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $to = $this->getEmailAddresses($email->getTo());
        $subject = $email->getSubject() ?? '';
        $body = $email->getTextBody() ?? $email->getHtmlBody() ?? '';
        
        // Get headers
        $headers = [];
        $headers[] = 'From: ' . $this->formatAddress($email->getFrom()[0] ?? null);
        
        if ($email->getReplyTo()) {
            $headers[] = 'Reply-To: ' . $this->formatAddress($email->getReplyTo()[0]);
        }
        
        if ($email->getCc()) {
            $headers[] = 'Cc: ' . $this->getEmailAddresses($email->getCc());
        }
        
        if ($email->getBcc()) {
            $headers[] = 'Bcc: ' . $this->getEmailAddresses($email->getBcc());
        }
        
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        
        if ($email->getHtmlBody()) {
            $boundary = uniqid('boundary_');
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            
            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= $email->getTextBody() ?? strip_tags($email->getHtmlBody());
            $body .= "\r\n\r\n--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $email->getHtmlBody();
            $body .= "\r\n\r\n--{$boundary}--";
        }
        
        $headerString = implode("\r\n", $headers);
        
        // Use PHP's native mail() function
        $result = @mail($to, $subject, $body, $headerString);
        
        if (!$result) {
            throw new \RuntimeException('Failed to send email using PHP mail() function');
        }
    }
    
    /**
     * Format email address for headers.
     */
    protected function formatAddress($address): string
    {
        if (!$address) {
            return '';
        }
        
        $email = $address->getAddress();
        $name = $address->getName();
        
        if ($name) {
            return $name . ' <' . $email . '>';
        }
        
        return $email;
    }
    
    /**
     * Get email addresses as string.
     */
    protected function getEmailAddresses(array $addresses): string
    {
        $emails = [];
        foreach ($addresses as $address) {
            $emails[] = $this->formatAddress($address);
        }
        return implode(', ', $emails);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return 'php-mail://';
    }
}

