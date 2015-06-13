<?php

namespace Barbare\Framework\Mail;

use Barbare\Framework\Mvc\Component;

class Mail extends Component
{
    public $sendTo = array();
    public $xheaders = array();
    public $receipt = 0;

    public function __construct()
    {
        //$this->config = new Storage($application->getConfig()->read('config'));
    }

    /*
    Define the subject line of the email
    @param string $subject any monoline string
    */
    public function setSubject($subject)
    {
        $this->xheaders['Subject'] = strtr($subject, "\r\n", "  ");
    }

    /*
    Set the sender of the mail
    @param string $from should be an email address
    */

    public function setSender($from)
    {
        if (! is_string($from)) {
            echo "Class Mail: error, From is not a string";
            exit;
        }
        $this->xheaders['From'] = $from;
    }

    /*
    Set the Reply-to header
    @param string $email should be an email address
    */
    public function setReplyTo($address)
    {
        if (! is_string($address)) {
            return false;
        }

        $this->xheaders["Reply-To"] = $address;
    }

    /*
    Set the mail recipient
    @param string $to email address, accept both a single address or an array of addresses
    */

    public function setReceipts($to)
    {
        if (is_array($to)) {
            $this->sendTo = $to;
        } else {
            $this->sendTo[] = $to;
        }
    }

    /*		Body( text [, charset] )
     *		set the body (message) of the mail
     *		define the charset if the message contains extended characters (accents)
     *		default to us-ascii
     *		$mail->Body( "mél en français avec des accents", "iso-8859-1" );
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /*
    Build the email message
    @access protected
    */
    public function buildMail()
    {

        // build the headers
        $this->headers = "";
        //	$this->xheaders['To'] = implode( ", ", $this->sendTo );


        if ($this->receipt) {
            if (isset($this->xheaders["Reply-To"])) {
                $this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
            } else {
                $this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
            }
        }

        $this->xheaders["Mime-Version"] = "1.0";
        $this->xheaders["Content-Type"] = "text/html; UTF-8";

        reset($this->xheaders);
        while (list($hdr, $value) = each($this->xheaders)) {
            if ($hdr != "Subject") {
                $this->headers .= "$hdr: $value\n";
            }
        }
    }
    /*
    format and send the mail
    @access public
    */
    public function send()
    {
        $this->buildMail();
        $this->strTo = implode(", ", $this->sendTo);
        // envoie du mail
        $res = @mail($this->strTo, $this->xheaders['Subject'], $this->body, $this->headers);
    }
}
