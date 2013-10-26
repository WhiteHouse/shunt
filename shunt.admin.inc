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
  $shunts = shunt_get_shunt_definitions();
  $values = $form_state['values'];
  foreach ($shunts as $name => $description) {
    // Set each shunt trip to enabled or disabled according to
    // which checkboxes the site admin checked.
    if ($values[$name]) {
      shunt_enable_shunt($name);
    }
    else {
      shunt_disable_shunt($name);
    }
  }
}
