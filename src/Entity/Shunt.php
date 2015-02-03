<?php

/**
 * @file
 * Contains \Drupal\shunt\Entity\Shunt.
 */

namespace Drupal\shunt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;

/**
 * Defines a shunt configuration entity.
 *
 * @ConfigEntityType(
 *   id = "shunt",
 *   label = @Translation("Shunt"),
 *   handlers = {
 *     "list_builder" = "Drupal\shunt\Controller\ShuntListBuilder",
 *     "form" = {
 *       "add" = "Drupal\shunt\Form\ShuntForm",
 *       "edit" = "Drupal\shunt\Form\ShuntForm",
 *       "delete" = "Drupal\shunt\Form\ShuntDeleteForm",
 *     }
 *   },
 *   config_prefix = "shunt",
 *   admin_permission = "administer shunts",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/development/shunts/{shunt}",
 *     "delete-form" = "/admin/config/development/shunts/{shunt}/delete",
 *   }
 * )
 */
class Shunt extends ConfigEntityBase implements ShuntInterface {

  /**
   * The shunt ID.
   *
   * @var string
   */
  public $id;

  /**
   * The shunt label.
   *
   * @var string
   */
  public $label;

  /**
   * The shunt description.
   *
   * @var string
   */
  public $description;

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    /** @var \Drupal\shunt\Entity\Shunt $shunt */
    foreach ($entities as $shunt) {
      $shunt->deleteState();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function urlInfo($rel = 'edit-form', array $options = []) {
    // The shunt has not yet been saved and so cannot have a URI. Pass through
    // to the parent class to handle the exception.
    if ($this->isNew()) {
      return parent::urlInfo($rel, $options);
    }

    switch ($rel) {
      case 'enable':
      case 'disable':
        return new Url('shunt.set_status', [
          'id' => $this->id(),
          'action' => $rel,
        ]);

      default:
        return parent::urlInfo($rel, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function isShuntEnabled() {
    return \Drupal::state()->get($this->getStatusStateKey(), FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function enableShunt() {
    $t_args = ['%id' => $this->id()];

    if ($this->isShuntEnabled()) {
      drupal_set_message(t('Shunt %id is already enabled.', $t_args), 'warning');
      return;
    }

    \Drupal::moduleHandler()->invokeAll('shunt_status_change', [$this, 'enable']);

    \Drupal::state()->set($this->getStatusStateKey(), TRUE);

    \Drupal::logger('shunt')->notice('Enabled shunt %id.', $t_args);
    drupal_set_message(t('Shunt %id has been enabled.', $t_args));
  }

  /**
   * {@inheritdoc}
   */
  public function disableShunt() {
    $t_args = ['%id' => $this->id()];

    if (!$this->isShuntEnabled()) {
      drupal_set_message(t('Shunt %id is already disabled.', $t_args), 'warning');
      return;
    }

    \Drupal::moduleHandler()->invokeAll('shunt_status_change', [$this, 'disable']);

    \Drupal::state()->set($this->getStatusStateKey(), FALSE);

    \Drupal::logger('shunt')->notice('Disabled shunt %id.', $t_args);
    drupal_set_message(t('Shunt %id has been disabled.', $t_args));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteState() {
    \Drupal::state()->delete($this->getStatusStateKey());
  }

  /**
   * Returns the key for the state value that stores the shunt's status.
   *
   * @return string
   *   A state key.
   */
  private function getStatusStateKey() {
    return "shunt.{$this->id()}";
  }

}
