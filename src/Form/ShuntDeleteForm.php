<?php

/**
 * @file
 * Contains \Drupal\example\Form\ShuntDeleteForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete a shunt.
 */
class ShuntDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the shunt %id?', [
      '%id' => $this->entity->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('shunt.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $args = ['%id' => $this->entity->id()];
    drupal_set_message($this->t('Shunt %id has been deleted.', $args));
    $this->logger('shunt')->notice('Deleted shunt %id.', $args);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
