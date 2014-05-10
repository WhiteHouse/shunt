<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\ShuntConfigForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Form\FormInterface;

/**
 * Provides a form for configuring available shunts.
 */
class ShuntConfigForm implements FormInterface {

  /**
   * @var \Drupal\shunt\ShuntManager
   *   The shunt manager.
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
  public function getFormID() {
    return 'shunt_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
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
        'name' => "<strong><label for=\"edit-shunts-{$name}\">{$name}</label></strong>",
        'provider' => $definition['provider'],
        'description' => $definition['description'],
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
  public function validateForm(array &$form, array &$form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->shuntManager->setShuntStatusMultiple($form_state['values']['shunts']);
  }

}
