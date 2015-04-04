<?php

/**
 * @file
 * Contains \Drupal\shunt\Entity\Shunt.
 */

namespace Drupal\shunt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
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
   * The protected flag.
   *
   * @var boolean
   */
  public $protected;

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    $this->deleteState();
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
      case 'delete':
        return new Url('entity.shunt.delete_form', [
          'shunt' => $this->id(),
        ]);

      case 'trip':
      case 'reset':
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
  public function isTripped() {
    return \Drupal::state()->get($this->getStatusStateKey(), FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function isProtected() {
    return $this->protected;
  }

  /**
   * {@inheritdoc}
   */
  public function trip() {
    $t_args = ['%id' => $this->id()];

    if ($this->isTripped()) {
      drupal_set_message(t('Shunt %id is already tripped.', $t_args), 'warning');
      return;
    }

    \Drupal::moduleHandler()->invokeAll('shunt_status_change', [$this, 'trip']);

    \Drupal::state()->set($this->getStatusStateKey(), TRUE);

    \Drupal::logger('shunt')->notice('Tripped shunt %id.', $t_args);
    drupal_set_message(t('Shunt %id has been tripped.', $t_args));
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $t_args = ['%id' => $this->id()];

    if (!$this->isTripped()) {
      drupal_set_message(t('Shunt %id is not tripped.', $t_args), 'warning');
      return;
    }

    \Drupal::moduleHandler()->invokeAll('shunt_status_change', [$this, 'reset']);

    \Drupal::state()->set($this->getStatusStateKey(), FALSE);

    \Drupal::logger('shunt')->notice('Reset shunt %id.', $t_args);
    drupal_set_message(t('Shunt %id has been reset.', $t_args));
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
