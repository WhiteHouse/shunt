<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\SetShuntStatusConfirmForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\shunt\Entity\Shunt;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form to set the status of a given shunt.
 */
class SetShuntStatusConfirmForm extends ConfirmFormBase {

  /**
   * The given shunt.
   *
   * @var \Drupal\shunt\Entity\Shunt
   */
  public $shunt;

  /**
   * The status action--"trip" or "reset".
   *
   * @var string
   */
  public $action;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shunt_set_status_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $t_args = ['%id' => $this->shunt->id()];
    if ($this->action == 'trip') {
      $message = t('Are you sure you want to trip the %id shunt?', $t_args);
    }
    else {
      $message = t('Are you sure you want to reset the %id shunt?', $t_args);
    }
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->shunt->getDescription();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if ($this->action == 'trip') {
      return t('Trip');
    }
    else {
      return t('Reset');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
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
  public function buildForm(array $form, FormStateInterface $form_state, $id = '', $action = '') {
    $shunt = Shunt::load($id);

    // Err if there is no such shunt.
    if (!$shunt) {
      throw new NotFoundHttpException();
    }

    $this->shunt = $shunt;
    $this->action = $action;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('shunt.list');

    if ($this->action == 'trip') {
      $this->shunt->trip();
    }
    else {
      $this->shunt->reset();
    }
  }
}
