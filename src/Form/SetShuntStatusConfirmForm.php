<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\SetShuntStatusConfirmForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form to set the status of a given shunt.
 */
class SetShuntStatusConfirmForm extends ConfirmFormBase {

  /**
   * The shunt manager.
   *
   * @var \Drupal\shunt\ShuntManager
   */
  public $shuntManager;

  /**
   * The given shunt machine name.
   *
   * @var string
   */
  public $shuntName;

  /**
   * The plugin definition for the given shunt.
   *
   * @var array
   */
  public $shuntDefinition;

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
    $args = array('@name' => $this->shuntName);
    if ($this->action == 'enable') {
      $message = t('Are you sure you want to enable the "@name" shunt?', $args);
    }
    else {
      $message = t('Are you sure you want to disable the "@name" shunt?', $args);
    }
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->shuntDefinition['description'];
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
    return new Url('shunt.config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $shunt = '', $action = '') {
    $this->shuntManager = \Drupal::service('plugin.manager.shunt');

    // Err if given shunt name is invalid.
    if (!$this->shuntManager->shuntExists($shunt)) {
      throw new NotFoundHttpException();
    }

    $this->shuntName = $shunt;
    $this->shuntDefinition = $this->shuntManager->getDefinition($shunt);
    $this->action = $action;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('shunt.config');

    $current_state = $this->shuntManager->shuntIsEnabled($this->shuntName);
    $target_state = ($this->action == 'enable');

    // Abort if the the shunt is already in the target state.
    if ($current_state == $target_state) {
      $args = array('@name' => $this->shuntName);
      if ($this->action == 'enable') {
        $message = t('Shunt "@name" is already enabled.', $args);
      }
      else {
        $message = t('Shunt "@name" is already disabled.', $args);
      }
      drupal_set_message($message, 'warning');
      return;
    }

    // Set shunt status.
    if ($target_state) {
      $this->shuntManager->enableShunt($this->shuntName);
    }
    else {
      $this->shuntManager->disableShunt($this->shuntName);
    }
  }
}
