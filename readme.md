## Skype History Log

This is a simple Skype call history log that I use for reporting all the time I
spend on calls during the day.  Skype used to have a decent call history, but
alas, it's nearly unusable for what I need anymore.

This is a command-line utility that uses AppleScript to communicate with Skype,
so this will only run on OS/X.

## Installation

    pear install console_commandline
    pear install console_table
    chmod +x skype-history.php

## Usage

    skype-history [options]

    Options:
      -t 60, --threshold=60                    Calls less than this amount of
                                               time (in seconds) will not be
                                               reported.
      -s "Last Monday", --since="Last Monday"  Only report calls that occurred
                                               after this time.  Can be almost
                                               any parseable date string.
      -h, --help                               show this help message and exit
      -v, --version                            show the program version and
                                               exit
