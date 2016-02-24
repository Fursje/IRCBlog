<?php

class irc {

  public $host   = "";
  public $port   = ;
  public $passwd = "";
  public $max_lenght = 500;
  public $available_channels = array('#gaap');

  public $message = array();
  public $channel = false;

  public function setChannel($channel) {
    if (!empty($channel) && in_array($channel,$this->available_channels)) {
      $this->channel = $channel;
      return true;
    } else { return false; }
  }
  public function addQueue($msg) {
    if (!empty($msg) && is_string($msg)) {
      $this->message[] = substr($msg,0,$this->max_lenght);
      return true;
    } else { return false; }
  }
  public function SendNow($msg,$channel,$err = false) {
    if (empty($channel) || !in_array($channel,$this->available_channels)) {
      $err = "Invalid channel.";
      return false;
    }
    if (empty($msg) || !is_string($msg)) {
      $err = "Invalid message.";
      return false;
    }
    if ($socket = socket_create(AF_INET,SOCK_DGRAM,SOL_UDP)) {
      if (socket_connect($socket,$this->host,$this->port)) {
        $packet = $this->passwd." ".serialize(array('command'=>'PRIVMSG','who'=>$channel,'text'=>substr($msg,0,$this->max_lenght)));
        if (FALSE === socket_write($socket,$packet,strlen($packet))) {
          $err = socket_strerror(socket_last_error($socket));
          return false;
        } else { return true; }
      } else {
          $err = socket_strerror(socket_last_error($socket));
          return false;
       }
    }
  }
  public function clearQueue() {
    $this->message = array();
  }
  public function Send($err = false) {
    if (count($this->message) > 0 && $this->channel !== FALSE) {
      $packet = false;
      if ($socket = socket_create(AF_INET,SOCK_DGRAM,SOL_UDP)) {
        if (socket_connect($socket,$this->host,$this->port)) {
          foreach ($this->message as $text) {
            $packet = $this->passwd." ".serialize(array('command'=>'PRIVMSG','who'=>$this->channel,'text'=>$text));
            if (FALSE === socket_write($socket,$packet,strlen($packet))) {
              $err = socket_strerror(socket_last_error($socket));
              return false;
            }
          }
          $this->clearQueue();
          return true;
        } else { 
          $err = socket_strerror(socket_last_error($socket));
          return false;
        }
      }
    } else { 
        $err = "Channel or no Message set."; 
        return false;
      }
  }

}

?>
