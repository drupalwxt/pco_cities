<?php

namespace Drupal\login_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Form\FormStateInterface;

class LoginRedirectConfigForm extends ConfigFormBase {

  public function getFormId() {
    return 'login_redirect_config';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('login_redirect.settings');

    $roles = user_role_names(TRUE);

    // Create an array of text fields for role-specific paths.
    foreach ($roles as $key => $role) {
      $form['roles[' . $key . ']'] = [
        '#type' => 'textfield',
        '#title' => $this->t($role),
        '#default_value' => $config->get('login_redirect.' . $key),
      ];
    }

    $form['default'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default'),
      '#default_value' => $config->get('login_redirect.default'),
    ];

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $roles = $_POST['roles'];

    $config = $this->config('login_redirect.settings');

    foreach ($roles as $role => $path) {
      $config->set('login_redirect.' . $role, $path);
    }

    $config->set('login_redirect.default', $form_state->getValue('default'));

    $config->save();

    drupal_set_message($this->t("Settings saved"));

    return parent::submitForm($form, $form_state);
  }

  protected function getEditableConfigNames() {

    return [

      'login_redirect.settings',

    ];

  }

}
