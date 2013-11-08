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
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'shunt_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $shunts = shunt_get_shunt_definitions();

    // Define table header.
    $header = array(
      'name' => t('Name'),
      'description' => t('Description'),
    );

    // Build table rows.
    $options = array();
    $default_values = array();
    foreach ($shunts as $name => $description) {
      $options[$name] = array(
        'name' => "<strong><label for=\"edit-shunts-{$name}\">{$name}</label></strong>",
        'description' => $description,
      );
      $default_values[$name] = shunt_is_enabled($name);
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
    _shunt_set_status_multiple($form_state['values']['shunts'], FALSE);
  }

}
