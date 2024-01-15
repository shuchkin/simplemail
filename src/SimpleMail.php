<?php

namespace Shuchkin;

use InvalidArgumentException;
use RuntimeException;

class SimpleMail
{
    public $toName;
    public $toEmail;
    public $ccEmail;
    public $fromName;
    public $fromEmail;
    public $replyName;
    public $replyEmail;
    public $subject;
    public $text;
    public $html;
    public $attachments;
    public $priority;
    public $customHeaders;
    public $transport;
    public $transportParams;
    public $listener;

    /**
     * Mail constructor.
     *
     * @param string|callable $transport internal supports "mail" and "smtp"
     * @param array $transportParams for "mail", for "smtp" supports params
     * ['host' => 'localhost','port' => 25, 'username' => '', 'password' => '', 'timeout' => 5 ]
     */
    public function __construct($transport = 'mail', array $transportParams = array())
    {
        $this->toName        = '';
        $this->toEmail       = '';
        $this->ccEmail       = '';
        $this->fromName      = '';
        $this->fromEmail     = '';
        $this->replyName     = '';
        $this->replyEmail    = '';
        $this->subject       = '';
        $this->text          = '';
        $this->html          = '';
        $this->attachments   = array();
        $this->priority      = '';
        $this->customHeaders = array();
        $this->setTransport($transport, $transportParams);
    }

    public function listen(callable $listener)
    {
        $old            = $this->listener;
        $this->listener = $listener;

        return $old;
    }

    /**
     * Fabric method to create a Mail instance with current mail settings
     *
     * @param string|array $email
     * @param string $name
     *
     * @return SimpleMail
     */
    public function to($email, $name = '')
    {
        $m          = clone $this;
        $m->toEmail = $email;
        $m->toName  = $name;

        return $m;
    }

    /**
     * Alternative fabric method to create Mail instances with current mail settings
     *
     * @param string $to Receiver email
     * @param string $subject Subject text
     * @param string $message Message text
     *
     * @return SimpleMail
     */
    public function compose($to = '', $subject = '', $message = '')
    {
        $m          = clone $this;
        $m->toEmail = $to;
        $m->subject = $subject;
        $m->text    = $message;

        return $m;
    }

    /**
     * Set receiver address and name
     *
     * @param string|array $email Valid email address/addresses
     * @param string $name Valid recipient name
     *
     * @return $this
     */
    public function setTo($email, $name = '')
    {
        $this->toEmail = $email;
        $this->toName  = $name;

        return $this;
    }

    public function getToEmail()
    {
        return $this->toEmail;
    }

    public function getToName()
    {
        return $this->toName;
    }

    public function setFrom($email, $name = '')
    {
        $this->fromEmail = $email;
        $this->fromName  = $name;

        return $this;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function setReplyTo($email, $name = false)
    {
        $this->replyEmail = $email;
        $this->replyName  = $name;

        return $this;
    }

    public function getReplyToEmail()
    {
        return $this->replyEmail;
    }

    public function getReplyToName()
    {
        return $this->replyName;
    }

    public function getCcEmail()
    {
        return $this->ccEmail;
    }

    public function setCcEmail($ccEmail)
    {
        $this->ccEmail = $ccEmail;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function addText($text)
    {
        $this->text .= ( $this->text === '' ? '' : "\r\n" ) . preg_replace('/\r\n?|\n/', "\r\n", $text);

        return $this;
    }

    public function getHTML()
    {
        return $this->html;
    }

    public function setHTML($html, $addAltText = false)
    {
        $this->html = $html;
        if ($addAltText) {
            $this->text = strip_tags($this->html);
        }

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority Can be 'normal', 'urgent', or 'non-urgent'
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function attach($attachment, $inlineFileName = '')
    {
        $this->attachments[ $inlineFileName ?: basename($attachment) ] = $attachment;

        return $this;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    public function setCustomHeaders(array $ch)
    {
        $this->customHeaders = $ch;

        return $this;
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function setTransport($transport, array $transportParams = array())
    {
        if ($transport === 'mail') {
            $this->transport       = 'mail';
            $this->transportParams = $transportParams;
        } elseif ($transport === 'smtp') {
            $this->transport       = 'smtp';
            $this->transportParams = array_merge(array(
                'host'     => 'localhost',
                'port'     => 25,
                'username' => '',
                'password' => '',
                'timeout'  => 5
            ), $transportParams);
        } elseif (is_callable($transport)) {
            $this->transport       = $transport;
            $this->transportParams = $transportParams;
        } else {
            throw new InvalidArgumentException('Invalid transport (mail, smtp or callable supported).');
        }

        return $this;
    }

    public function getTransportParams()
    {
        return $this->transportParams;
    }

    /**
     * Sends the composed mail with the selected transport.
     * @return bool
     */
    public function send()
    {

        if ($this->listener) {
            call_user_func($this->listener, 'sendmail from: ' . $this->fromEmail . ' to: ' . $this->toEmail . ' subject: ' . $this->subject);
        }

        $result = false;

        if (! $this->toEmail) {
            throw new InvalidArgumentException('Error: E-Mail to required!');
        }
        if (! $this->fromEmail) {
            throw new InvalidArgumentException('Error: E-Mail from required!');
        }
        if (! $this->subject) {
            throw new InvalidArgumentException('Error: E-Mail subject required!');
        }
        if (! $this->text && ! $this->html) {
            throw new InvalidArgumentException('Error: E-Mail message required!');
        }
        if ($this->priority && ! in_array($this->priority, [ 'normal', 'urgent', 'non-urgent' ], true)) {
            throw new InvalidArgumentException("Priority possible values 'normal', 'urgent' or 'non-urgent'");
        }

        if (strtoupper(0 === strpos(PHP_OS, 'WIN'))) {
            $eol = "\r\n";
        } elseif (strtoupper(0 === strpos(PHP_OS, 'MAC'))) {
            $eol        = "\r";
            $this->text = str_replace("\r\n", "\r", $this->text);
            $this->html = str_replace("\r\n", "\r", $this->html);
        } else {
            $eol        = "\n";
            $this->text = str_replace("\r\n", "\n", $this->text);
            $this->html = str_replace("\r\n", "\n", $this->html);
        }
        $headers = 'X-Mailer: ' . __CLASS__ . ' PHP/' . PHP_VERSION . $eol;

        if (is_array($this->toEmail)) {
            $to = implode(', ', $this->toEmail);
        } elseif ($this->toName) {
            if (preg_match('/^[a-zA-Z0-9\-\. ]+$/', $this->toName)) {
                $to = $this->toName . ' <' . $this->toEmail . '>';
            } else {
                $to = '=?UTF-8?B?' . base64_encode($this->toName) . '?= <' . $this->toEmail . '>';
            }
        } else {
            $to = $this->toEmail;
        }

        $to_header = 'To: ' . $to . $eol;

        if ($this->ccEmail) {
            $headers .= 'Cc: ' . $this->ccEmail . $eol;
        }

        if (preg_match('/^[a-zA-Z0-9\-\. ]+$/', $this->subject)) {
            $subject = $this->subject;
        } else {
            $subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';
        }

        $subject_header = 'Subject: ' . $subject . $eol;

        $message = '';

        $type = ( $this->html && $this->text ) ? 'alt' : 'plain';
        $type .= count($this->attachments) ? '_attachments' : '';

        $headers .= 'MIME-Version: 1.0' . $eol;

        $from = $this->fromEmail;
        if ($this->fromName) {
            if (preg_match('/^[a-zA-Z0-9\-\. ]+$/', $this->fromName)) {
                $from = $this->fromName . ' <' . $from . '>';
            } else {
                $from = '=?UTF-8?B?' . base64_encode($this->fromName) . '?= <' . $from . '>';
            }
        }
        $headers .= 'From: ' . $from . $eol;

        $replyTo = $from;
        if ($this->replyEmail) {
            $replyTo = $this->replyEmail;
            if ($this->replyName) {
                if (preg_match('/^[a-zA-Z0-9\-\. ]+$/', $this->replyName)) {
                    $replyTo = $this->replyName . ' <' . $replyTo . '>';
                } else {
                    $replyTo = '=?UTF-8?B?' . base64_encode($this->replyName) . '?= <' . $replyTo . '>';
                }
            }
        }
        $headers .= 'Reply-To: ' . $replyTo . $eol;

        $headers .= 'Date: ' . gmdate('D, d M Y H:i:s O') . $eol;

        if ($this->priority) {
            $headers .= 'Priority: ' . $this->priority . $eol;
        }
        if (count($this->customHeaders)) {
            foreach ($this->customHeaders as $k => $v) {
                $headers .= $k . ': ' . $v . $eol;
            }
        }

        //      $headers .= 'Content-Type: multipart/alternative; charset=UTF-8; format=flowed; delsp=yes; boundary="' . $boundary . '"'.$eol.$eol;

        switch ($type) {
            case 'plain':
                $headers .= 'Content-Type: ' . ( $this->html ? 'text/html' : 'text/plain' ) . '; charset="UTF-8"';
                $message .= $this->html ?: $this->text;
                break;
            case 'alt':
                $boundary = md5(uniqid(time(), true));

                $headers .= 'Content-Type: multipart/alternative; format=flowed; delsp=yes; boundary="' . $boundary . '"';

                $message .= '--' . $boundary . $eol;
                $message .= 'Content-Type: text/plain; charset="UTF-8"' . $eol;
                $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                $message .= chunk_split(base64_encode($this->text), 76, $eol);
                $message .= $eol . '--' . $boundary . $eol;
                $message .= 'Content-Type: text/html; charset="UTF-8"' . $eol;
                $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                $message .= chunk_split(base64_encode($this->html), 76, $eol);
                $message .= $eol . '--' . $boundary . '--';
                break;
            case 'plain_attachments':
                $boundary = md5(uniqid(time(), true));

                $headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

                $message .= '--' . $boundary . $eol;
                if ($this->text) {
                    $message .= 'Content-Type: text/plain; charset="UTF-8"' . $eol;
                    $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                    $message .= chunk_split(base64_encode($this->text), 76, $eol);
                } else {
                    $message .= 'Content-Type: text/html; charset="UTF-8"' . $eol;
                    $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                    $message .= chunk_split(base64_encode($this->html), 76, $eol);
                }
                foreach ($this->attachments as $basename => $fullname) {
                    $content = file_get_contents($fullname);
                    $message .= $eol . '--' . $boundary . $eol;
                    $message .= 'Content-Type: application/octetstream' . $eol;
                    $message .= 'Content-Transfer-Encoding: base64' . $eol;
                    $message .= 'Content-Disposition: attachment; filename="' . $basename . '"' . $eol;
                    $message .= 'Content-ID: <' . $basename . '>' . $eol . $eol;
                    $message .= chunk_split(base64_encode($content), 76, $eol);
                }
                $message .= $eol . '--' . $boundary . '--';
                break;
            case 'alt_attachments':
                $boundary  = md5(uniqid(time(), true));
                $boundary2 = 'bd2_' . $boundary;

                $headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

                $message .= '--' . $boundary . $eol;
                $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . $eol . $eol;
                $message .= '--' . $boundary2 . $eol;
                $message .= 'Content-Type: text/plain; charset="UTF-8"' . $eol;
                $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                $message .= chunk_split(base64_encode($this->text), 76, $eol);
                $message .= $eol . '--' . $boundary2 . $eol;
                $message .= 'Content-Type: text/html; charset="UTF-8"' . $eol;
                $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
                $message .= chunk_split(base64_encode($this->html), 76, $eol);
                $message .= $eol . '--' . $boundary2 . '--';

                foreach ($this->attachments as $basename => $fullname) {
                    $content = file_get_contents($fullname);
                    $message .= $eol . '--' . $boundary . $eol;
                    $message .= 'Content-Type: application/octetstream' . $eol;
                    $message .= 'Content-Transfer-Encoding: base64' . $eol;
                    $message .= 'Content-Disposition: attachment; filename="' . $basename . '"' . $eol;
                    $message .= 'Content-ID: <' . $basename . '>' . $eol . $eol;
                    $message .= chunk_split(base64_encode($content), 76, $eol);
                }
                $message .= $eol . '--' . $boundary . '--';
        }

        // Mail
        if ($this->transport === 'mail') {
            ini_set('sendmail_from', $this->fromEmail);
            $params = sprintf('-f %s -r %s', $this->fromEmail, $this->replyEmail ?: $this->fromEmail);
            foreach ($this->transportParams as $k => $v) {
                $params .= ' ' . $k . ' ' . $v;
            }
            $result = mail($to, $subject, $message, $headers, $params);
        } else if ($this->transport === 'smtp') { // SMTP
            $headers = $to_header . $subject_header . $headers;


            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]);

            $rs = strpos($this->transportParams['host'], 'unix:') === 0 ? $this->transportParams['host'] : $this->transportParams['host'] . ':' . $this->transportParams['port'];

            $fp = stream_socket_client($rs, $errno, $errstr, $this->transportParams['timeout'], STREAM_CLIENT_CONNECT, $context);
//              $fp = fsockopen( $this->transportParams['host'], $this->transportParams['port'], $errno, $errstr, $this->transportParams['timeout'] );

            if ($fp) {
                if (! empty($_SERVER['SERVER_NAME'])) {
                    $server = $_SERVER['SERVER_NAME'];
                } else {
                    list( , $server ) = explode('@', $this->fromEmail);
                }

                $lines = [ 'HELO ' . $server ];
                if (! empty($this->transportParams['username'])) {
                    $lines[] = 'AUTH LOGIN';
                    $lines[] = base64_encode($this->transportParams['username']);
                    $lines[] = base64_encode($this->transportParams['password']);
                }
                $lines[] = 'MAIL FROM: <' . $this->fromEmail . '>';
//                  $lines[] = 'MAIL FROM: '.$from;
                $rcpts = is_array($this->toEmail) ? $this->toEmail : [ $this->toEmail ];
                foreach ($rcpts as $rcpt) {
                    $lines[] = 'RCPT TO: <' . $rcpt . '>';
                }
                $lines[] = 'DATA';
                $lines[] = $headers . $eol . $eol
                           . $message . $eol . '.';
                $lines[] = 'QUIT';
                $sent    = 0;
                foreach ($lines as $line) {
//                      $ts = microtime(true);
                    $data = '';
                    $s    = '';
                    /** @noinspection SubStrUsedAsArrayAccessInspection */
                    while (is_resource($fp) and ! feof($fp) && substr($s, 3, 1) !== ' ') {
                        $s = fgets($fp, 1024);
                        if ($s === false) {
                            throw new RuntimeException('SMTP server is disconnected');
                        }
//                          print_r($s);

                        $data .= $s;
                        /*
                        if ( (microtime(true)-$ts) > 5 ) {
                            throw new \RuntimeException( 'Timeout 5' );
                            break;
                        }
                        */
                    }

                    if (strpos($data, '5') === 0) {
                        throw new RuntimeException($data);
                    }
//                      print_r( $line );
                    if (! fwrite($fp, $line . $eol)) {
                        throw new RuntimeException('SMTP socket write error');
                    }

                    $sent ++;
                    //                  echo $s;
                }
                if ($sent === count($lines)) {
                    $result = true;
                }

                fclose($fp);
            } else {
                throw new RuntimeException('Socket error: ' . $errstr);
            }
        } else if (is_callable($this->transport)) { // Custom transport
            // Send encoded subject, message, headers
            $headers = $to_header . $subject_header . $headers;
            $encoded = [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'message' => $message,
                'headers' => $headers
            ];
            $result  = call_user_func($this->transport, $this, $encoded);
        }

        return $result;
    }

    public function fromArray($a)
    {
        $m = clone $this;
        foreach ($a as $k => $v) {
            if (property_exists($m, $k)) {
                $m->{$k} = $v;
            }
        }

        return $m;
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        return array(
            'toName'        => $this->toName,
            'toEmail'       => $this->toEmail,
            'fromName'      => $this->fromName,
            'fromEmail'     => $this->fromEmail,
            'replyName'     => $this->replyName,
            'replyEmail'    => $this->replyEmail,
            'subject'       => $this->subject,
            'text'          => $this->text,
            'html'          => $this->html,
            'attachments'   => $this->attachments,
            'priority'      => $this->priority,
            'customHeaders' => $this->customHeaders
        );
    }

    public function fromJSON($json)
    {
        $m = clone $this;
        if ($j = json_decode($json, true)) {
            foreach ($j as $k => $v) {
                if (property_exists($m, $k)) {
                    $m->{$k} = $v;
                }
            }
        }

        return $m;
    }
}
