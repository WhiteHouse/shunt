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
  // Add checkboxes for shunt trips.
  $shunts = shunt_get_shunt_definitions();
  foreach ($shunts as $shunt => $description) {
    $form[$shunt] = array(
      '#type' => 'checkbox',
      '#title' => $shunt,
      '#default_value' => shunt_is_enabled($shunt),
      '#description' => $description,
    );
  }
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
    '#submit' => array('shunt_admin_settings_form_submit'),
  );

  return $form;
}

/**
 * Form submission handler for shunt_admin_settings_form().
 */
function shunt_admin_settings_form_submit($form, &$form_state) {
  $shunts = shunt_get_shunt_definitions();
  $changes = array_intersect_key($form_state['values'], $shunts);
  _shunt_set_status_multiple($changes, FALSE);
}
