<?php

namespace Drupal\exo_config\Config;

use Drupal\config_readonly\Config\ConfigReadonlyStorage;

/**
 * Defines the ConfigReadonly storage controller which will fail on write.
 */
class ExoConfigReadonlyStorage extends ConfigReadonlyStorage {

  /**
   * {@inheritdoc}
   */
  protected function checkLock($name = '') {
    \Drupal::logger('exo_config')->debug('Check lock on: ' . $name);
    if (exo_config_lock()) {
      parent::checkLock($name);
    }
  }

}
