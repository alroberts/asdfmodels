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
        $fromAddress = $email->getFrom()[0] ?? null;
        $fromEmail = $fromAddress ? $fromAddress->getAddress() : null;
        
        // Set From header
        $headers[] = 'From: ' . $this->formatAddress($fromAddress);
        
        // Set Return-Path to match From address to avoid policy violations
        // This ensures the envelope sender matches the From header
        if ($fromEmail) {
            $headers[] = 'Return-Path: <' . $fromEmail . '>';
        }
        
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
        
        // Set Content-Type header only once, based on whether HTML content exists
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
        } else {
            // Plain text only
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }
        
        $headerString = implode("\r\n", $headers);
        
        // Use PHP's native mail() function
        // The -f parameter sets the envelope sender (Return-Path) to match the From address
        // This prevents policy violations when the email is relayed through external SMTP servers
        // Format: "-f email@address.com" (space after -f is required)
        $additionalParams = '';
        if ($fromEmail) {
            // Escape the email address for shell safety, but keep the -f format correct
            $additionalParams = '-f ' . escapeshellarg($fromEmail);
        }
        
        $result = @mail($to, $subject, $body, $headerString, $additionalParams);
        
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

