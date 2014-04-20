<?php

/**
 * @file
 * Contains \Drupal\shunt\Form\ShuntConfigForm.
 */

namespace Drupal\shunt\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\shunt\ShuntHandler;

/**
 * Provides a form for configuring available shunts.
 */
class ShuntConfigForm implements FormInterface {

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
    $shunt_info = ShuntHandler::getDefinitions();

    // Define table header.
    $header = array(
      'name' => t('Name'),
      'description' => t('Description'),
    );

    // Build table rows.
    $options = array();
    $default_values = array();
    foreach ($shunt_info as $name => $description) {
      $options[$name] = array(
        'name' => "<strong><label for=\"edit-shunts-{$name}\">{$name}</label></strong>",
        'description' => $description,
      );
      $default_values[$name] = ShuntHandler::isEnabled($name);
    }

    // Compile table.
    $form['shunts'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#default_value' => $default_values,
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
    ShuntHandler::setStatusMultiple($form_state['values']['shunts']);
  }

}
