#!/usr/bin/env php
<?php

@include_once 'Console/Table.php';
@include_once 'Console/CommandLine.php';

function check_dependencies() {
  $success = TRUE;
  if (!class_exists('Console_CommandLine')) {
    printf("Console/CommandLine library from PEAR is required.\n");
    printf("  pear install console_commandline\n");
    $success = FALSE;
  }
  if (!class_exists('Console_Table')) {
    printf("Console/Table library from PEAR is required.\n");
    printf("  pear install console_table\n");
    $success = FALSE;
  }

  return $success;
}

class SkypeAPI {
  static function sendCommand($command) {
    // osascript -e 'tell application "Skype" to send command "SEARCH CALLS" script name "My Script"' 2>/dev/null
    $osa = 'tell application "Skype" to '
         . 'send command "' . $command .'" '
         . 'script name "My Script"';
    $command = "osascript -e '$osa' 2>/dev/null";
    return trim(shell_exec($command));
  }
}

class SkypeCall {
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
    $value = SkypeAPI::sendCommand($command);
    return trim(str_replace("CALL {$this->id} {$property}", '', $value));
  }

  static public function getCalls() {
    $calls = SkypeAPI::sendCommand('SEARCH CALLS');
    $calls = str_replace('CALLS', '', $calls);
    $calls = array_map('trim', explode(',', trim($calls)));
    return $calls;
  }
}

/**
 * Format a comma separated list of participants that approximately fits in the
 * specified length.
 */
function format_participants($participants, $length = 40) {
  $count = count($participants);
  if ($count == 1) {
    return $participants[0];
  }

  $output = '';
  for ($i = 0; $i < $count; $i++) {
    $p = $participants[$i];
    $remaining = $count - $i;

    if (empty($output)) {
      $tmp = $p;
    }
    elseif ($remaining == 1) {
      $tmp = "{$output} & {$p}";
    }
    else {
      $tmp = "{$output}, {$p}";
    }

    if (strlen($tmp) > $length) {
      if ($remaining == 1) {
        return "{$output} & 1 other";
      }
      return "{$output} & {$remaining} others";
    }

    $output = $tmp;
  }
  return $output;
}

/**
 * Based on Drupal function of the same name.
 * http://api.drupal.org/api/drupal/includes%21common.inc/function/format_interval/7
 */
function format_interval($interval, $granularity = 2, $langcode = NULL) {
  $units = array(
    '1 year|@count years' => 31536000,
    '1 month|@count months' => 2592000,
    '1 week|@count weeks' => 604800,
    '1 day|@count days' => 86400,
    '1 hour|@count hours' => 3600,
    '1 min|@count min' => 60,
    '1 sec|@count sec' => 1,
  );
  $output = '';
  foreach ($units as $key => $value) {
    $key = explode('|', $key);
    if ($interval >= $value) {
      $count = floor($interval / $value);
      $output .= ($output ? ' ' : '') . (($count == 1) ? $key[0] : strtr($key[1], array('@count' => $count)));
      $interval %= $value;
      $granularity--;
    }

    if ($granularity == 0) {
      break;
    }
  }

  return $output ? $output : '0 sec';
}

function skype_history($threshold = 60, $since = "Last Monday") {
  $calls = SkypeCall::getCalls();
  $since = strtotime($since);

  $table = new Console_Table();
  $table->setHeaders(array('Date', 'Time', 'Duration', '<>', 'Participants'));

  $types = array(
    'INCOMING_PSTN' => '<-',
    'INCOMING_P2P' => '<-',
    'OUTGOING_PSTN' => '->',
    'OUTGOING_P2P' => '->',
  );

  $previous_date = NULL;
  $total_duration = 0;

  printf("Gathering skype call history, this may take a few seconds.\n");
  foreach ($calls as $i => $id) {

    $call = new SkypeCall($id);

    // Bail out once the cutoff is reached.
    if ($call->timestamp < $since) {
      break;
    }

    if ($call->duration < $threshold) {
      continue;
    }

    $participants = array();
    $participants[] = $call->PARTNER_DISPNAME;

    if ($call->conf_participants_count > 1) {
      for ($j = 1; $j < (int)$call->conf_participants_count; $j++) {
        $p = $call->getProperty("CONF_PARTICIPANT ${j}");
        // drewstephens0815 OUTGOING_P2P FINISHED Andrew Stephens
        list($nick, $type, $status, $display_name) = explode(" ", $p, 4);
        $participants[] = $display_name;
      }
    }

    $date = date('D M d,Y', (int)$call->timestamp);
    if ($date == $previous_date) {
      $date = '';
    }
    else {
      if (!empty($previous_date)) {
        $table->addSeparator();
      }
      $previous_date = $date;
    }

    $total_duration += (int)$call->duration;

    $table->addRow(array(
      $date,
      date('g:ia', (int)$call->timestamp),
      format_interval($call->duration),
      $types[$call->type],
      format_participants($participants),
    ));

    printf("\r%s", str_repeat('.', $i));
  }

  printf("\r");
  print $table->getTable();

  printf("Number of calls: %s\n", count($calls));
  printf("Total duration: %s\n", format_interval($total_duration));
}


function main() {
  if (!check_dependencies()) {
    exit(1);
  }

  $parser = new Console_CommandLine();
  $parser->description = 'Skype History - A more useful skype call history.';
  $parser->version = '0.0.1';
  $parser->addOption('threshold', array(
      'short_name'  => '-t',
      'long_name'   => '--threshold',
      'description' => 'Calls less than this amount of time (in seconds) will not be reported.',
      'help_name'   => '60',
      'action'      => 'StoreInt',
      'default'     => 60,
  ));
  $parser->addOption('since', array(
      'short_name'  => '-s',
      'long_name'   => '--since',
      'description' => 'Only report calls that occurred after this time.  Can be almost any parseable date string.',
      'help_name'   => '"Last Monday"',
      'action'      => 'StoreString',
      'default'     => 'Last Monday',
  ));

  date_default_timezone_set('America/New_York');

  try {
      $app = $parser->parse();
      skype_history($app->options['threshold'], $app->options['since']);
  }
  catch (Exception $e) {
      $parser->displayError($e->getMessage());
      exit(1);
  }
}

main();
