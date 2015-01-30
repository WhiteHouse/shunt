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
   * The status action--"enable" or "disable".
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
    $args = ['%id' => $this->shunt->id()];
    if ($this->action == 'enable') {
      $message = t('Are you sure you want to enable the %id shunt?', $args);
    }
    else {
      $message = t('Are you sure you want to disable the %id shunt?', $args);
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
    if ($this->action == 'enable') {
      return t('Enable');
    }
    else {
      return t('Disable');
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

    if ($this->action == 'enable') {
      $this->shunt->enableShunt();
    }
    else {
      $this->shunt->disableShunt();
    }
  }
}
