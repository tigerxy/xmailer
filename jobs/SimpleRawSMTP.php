<?php
namespace Concrete\Package\Xmailer\Job;

class SimpleRawSMTP {
    const DEBUG = false;
    private $socket;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $connected;
    private $errno;
    private $errstr;

    public function __construct( $config ) {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->user = $config['user'];
        $this->pass = $config['password'];
        $this->$connected = false;
    }

    public function __destruct() {
        if ($this->$connected) {
            $this->sendCommandBute( 'QUIT' );
            fclose( $this->socket );
        }
    }

    private function connect() {
        $this->log('ssl://'.$this->host.':'.$this->port);
        $this->socket = fsockopen( 'ssl://'.$this->host, $this->port, $this->$errno, $this->$errstr, 3 );
        $this->log($this->socket);
        $this->readBuffer();
        $commands = [
            [250, 'NOOP'],
            [250, 'HELO '.$this->host],
            [334, 'AUTH LOGIN'],
            [334, base64_encode( $this->user )],
            [235, base64_encode( $this->pass )],
            [250, 'NOOP']
        ];
        try {
            $this->sendCommands( $commands );
            $this->$connected = true;
        } catch (\Exception $th) {
            fclose( $this->socket );
            throw new \Exception( 'SMTP Connection Error' );
        }
    }

    private function parseAddressEnvelope( $address ) {
        $pattern = '/^(?:"?((?:[^"\\\\]|\\\\.)+)"?\s)?<?([a-z0-9._%-]+@[a-z0-9.-]+\\.[a-z]{2,4})>?$/i';
        preg_match( $pattern, $address, $matches );
        return '<'.$matches[2].'>';
    }

    public function resetStatus() {
        $this->sendCommandBute( 'RSET' );
    }

    public function sendMail( $from, $to, $message ) {
        if (!$this->$connected) {
            $this->connect();
        }
        /*$message = "From: <bob@xxx.de>
        To: <alice@xxx.de>
        Subject: Testmail
        Date: Fri, 7 Aug 2020 20:10:50 +0200
        
        Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua.";*/
        $commands = [
            [250, 'MAIL FROM:'.$this->parseAddressEnvelope( $from )],
            [250, 'RCPT TO:'.$this->parseAddressEnvelope( $to )],
            [354, 'DATA'],
            [250, $message."\r\n."]
        ];
        try {
            $this->sendCommands( $commands );
        } catch (\Exception $th) {
            $this->resetStatus();
            throw new \Exception( 'SMTP Sending Error');
        }
    }

    private function sendCommands( $data ) {
        foreach ( $data as $c ) {
            $this->sendCommand( $c );
        }
    }

    private function sendCommand( $command ) {
        if ( !$this->socket ) {
            //echo "$this->$errstr ($this->$errno)<br/>\n";
            //echo $this->socket;
            throw new \Exception("$this->$errstr ($this->$errno)<br/>\n");
        } else {
            $code = $this->sendCommandBute( $command[1] );
            if ( $command[0] != $code ) {
                throw new \Exception(substr( $command[1], 0, 20 ).' expected as return code '.$command[0].' but got '.$code);
            }
        }
    }

    private function sendCommandBute( $command ) {
        $this->writeBuffer( "$command\r\n" );
        return $this->readBuffer();
    }

    private function writeBuffer($data) {
        $this->log($data);
        fwrite( $this->socket, $data );
    }

    private function readBuffer() {
        $buffer = fgets( $this->socket, 256 );
        $this->log($buffer);
        $code = intval( substr( $buffer, 0, 3 ) );
        return $code;
    }

    private function log($msg) {
        if (self::DEBUG) {
            echo $msg.PHP_EOL;
        }
    }
}