#!/usr/bin/php
<?php

/* 
 * ECS518U January 2017
 * Lab 4 zzz 
 *
 * Snooze for a while
 */

// The snooze time, or for ever
if ($argc > 1) {
    $numSec = $argv[1] ;
    $inc = 1 ;
} else {
    $numSec = 1 ;
    $inc = 0 ;
}

if ($numSec % 2) {
  $returnCode = 0 ;
}
else {
  $returnCode = 1 ;
}

$cnt = 0 ;

//print("$numSec $inc $cnt \n") ;

while ($cnt < $numSec) {
  //  print("$numSec $inc $cnt \n") ;
  echo("z ") ;
  $cnt += $inc ;

  // wait for a second
  sleep(1) ;
}

print("\n") ;
exit($returnCode) ;


?>
