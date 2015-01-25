<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\ShuntEditForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\shunt\Entity\Shunt;

/**
 * Provides an add/edit form for shunts.
 */
class ShuntForm extends EntityForm {

  /**
   * The shunt entity being used for this form.
   *
   * @var \Drupal\shunt\Entity\ShuntInterface
   */
  public $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $shunt = $this->entity;

    // Set title.
    if ($this->operation == 'add') {
      $form['#title'] = String::checkPlain($this->t('Add shunt'));
    }
    else {
      $form['#title'] = $this->t('Edit %id shunt', array(
        '%id' => $shunt->id(),
      ));
    }

    $form['label'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $shunt->label(),
      '#description' => t('The human-readable label for this shunt.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $shunt->id(),
      '#machine_name' => array(
        'exists' => array($this, 'shuntExists'),
        'source' => array('label'),
      ),
      '#disabled' => !$shunt->isNew(),
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $shunt->getDescription(),
      '#description' => t('Describe this shunt and the effect of enabling it.'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $shunt = $this->entity;
    $status = $shunt->save();

    $args = array('%id' => $shunt->id());
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('Shunt %id has been updated.', $args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('Shunt %id has been added.', $args));
      $this->logger('shunt')->notice('Added shunt %id.', $args);
    }

    $form_state->setRedirect('shunt.list');
  }

  /**
   * Determines whether a shunt with a given ID exists.
   *
   * @param string $id
   *   A shunt ID.
   *
   * @return bool
   *   True if the given shunt ID exists or FALSE if not.
   */
  public function shuntExists($id) {
    return (bool) Shunt::load($id);
  }

}
