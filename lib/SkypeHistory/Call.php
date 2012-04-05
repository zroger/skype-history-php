<?php

class SkypeHistory_Call {
  static public $__known_properties = array(
    'TIMESTAMP',
    'PARTNER_HANDLE',
    'PARTNER_DISPNAME',
    'TARGET_IDENTITY',
    'CONF_ID',
    'TYPE',
    'STATUS',
    'VIDEO_STATUS',
    'VIDEO_SEND_STATUS',
    'VIDEO_RECEIVE_STATUS',
    'FAILUREREASON',
    'SUBJECT',
    'PSTN_NUMBER',
    'DURATION',
    'PSTN_STATUS',
    'CONF_PARTICIPANTS_COUNT',
    'CONF_PARTICIPANT n',
    'VM_DURATION',
    'VM_ALLOWED_DURATION',
    'RATE',
    'RATE_CURRENCY',
    'RATE_PRECISION',
    'INPUT',
    'OUTPUT',
    'CAPTURE_MIC',
    'VAA_INPUT_STATUS',
    'FORWARDED_BY',
    'TRANSFER_ACTIVE',
    'TRANSFER_STATUS',
    'TRANSFERRED_BY',
    'TRANSFERRED_TO',
  );

  public $id;
  protected $__properties;

  public function __construct($id) {
    $this->id = $id;
  }

  public function __get($name) {
    $name = strtoupper($name);
    if (!isset($this->__properties[$name])) {
      $this->__properties[$name] = $this->getProperty($name);
    }
    return $this->__properties[$name];
  }

  public function getProperty($property) {
    $command = "GET CALL {$this->id} {$property}";
    $value = static::sendCommand($command);
    return trim(str_replace("CALL {$this->id} {$property}", '', $value));
  }

  static function sendCommand($command) {
    // osascript -e 'tell application "Skype" to send command "SEARCH CALLS" script name "My Script"' 2>/dev/null
    $osa = 'tell application "Skype" to '
         . 'send command "' . $command .'" '
         . 'script name "My Script"';
    $command = "osascript -e '$osa' 2>/dev/null";
    return trim(shell_exec($command));
  }

  static public function getCalls() {
    $calls = static::sendCommand('SEARCH CALLS');
    $calls = str_replace('CALLS', '', $calls);
    $calls = array_map('trim', explode(',', trim($calls)));
    return $calls;
  }
}

