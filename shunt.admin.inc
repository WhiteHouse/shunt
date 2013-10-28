<?php

/**
 * @file
 * Admin page callback file for the Shunt module.
 */

/**
 * Page callback: Form constructor for the admin settings form.
 *
 * @see shunt_menu()
 * @see shunt_admin_settings_form_submit()
 *
 * @ingroup forms
 */
function shunt_admin_settings_form() {
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
  $form['#submit'][] = 'shunt_admin_settings_form_submit';

  return $form;
}

/**
 * Form submission handler for shunt_admin_settings_form().
 */
function shunt_admin_settings_form_submit($form, &$form_state) {
  _shunt_set_status_multiple($form_state['values']['shunts'], FALSE);
}
