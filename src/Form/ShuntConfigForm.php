<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\ShuntConfigForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\String;

/**
 * Provides a form for configuring available shunts.
 */
class ShuntConfigForm implements FormInterface {

  /**
   * The shunt manager.
   *
   * @var \Drupal\shunt\ShuntManager
   */
  protected $shuntManager;

  /**
   * Constructs a ShuntConfigForm object.
   */
  public function __construct() {
    $this->shuntManager = \Drupal::service('plugin.manager.shunt');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shunt_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Define table header.
    $header = array(
      'name' => t('Name'),
      'provider' => t('Provider'),
      'description' => t('Description'),
    );

    // Build table rows.
    $options = array();
    $default_values = array();
    foreach ($this->shuntManager->getDefinitions() as $name => $definition) {
      $options[$name] = array(
        'name' => String::checkPlain($name),
        'provider' => $definition['provider'],
        'description' => String::checkPlain($definition['description']),
      );
      $default_values[$name] = $this->shuntManager->shuntIsEnabled($name);
    }

    // Compile table.
    $form['shunts'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#default_value' => $default_values,
      '#empty' => t('No shunts available.'),
    );

    // Add submit button and handler.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $shunts = $form_state->getValue('shunts');
    $this->shuntManager->setShuntStatusMultiple($shunts);
  }

}
