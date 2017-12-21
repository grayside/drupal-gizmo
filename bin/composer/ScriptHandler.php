<?php

/**
 * @file
 * Contains \DrupalGizmo\Composer\ScriptHandler.
 */

namespace DrupalGizmo\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;;

class ScriptHandler {

  /**
   * Wraps drupal-project/drupal-scaffold script to run less frequently.
   *
   * @param \Composer\Script\Event $event
   *  Script event.
   */
  public static function runDrupalScaffoldIfNoFiles(Event $event) {
    $base_path = $event->getComposer()->getPackage()->getExtra()['gizmo']['drupal-path'];

    $fs = new Filesystem();
    if (!$fs->exists($base_path . '/index.php')) {
      $process = new Process('composer run-script drupal-scaffold');
      $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
    }
    else {
      $event->getIO()->write('Scaffolded "index.php" detected, skipping execution of drupal-scaffold');
    }
  }

  /**
   * Simpley test that these scripts can load.
   *
   * @param \Composer\Script\Event $event
   *  Script event.
   */
  public static function boom(Event $event) {
    $event->getIO()->write('<error>BOOM</error>');
  }

}
