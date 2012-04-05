<?php

function skype_history($threshold = 60, $since = "Last Monday") {
  $calls = SkypeHistory_Call::getCalls();
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

    $call = new SkypeHistory_Call($id);

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

