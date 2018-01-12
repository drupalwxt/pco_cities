<?php

namespace Drupal\login_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Form\FormStateInterface;

class LoginRedirectConfigForm extends ConfigFormBase
{
  public function getFormId()
  {

    return 'login_redirect_config';

  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('login_redirect.settings');

    $roles = user_role_names(TRUE);

    // create an array of text fields for role-specific paths
    foreach ($roles as $key => $role) {
      $form['roles[' . $key . ']'] = array(
        '#type' => 'textfield',
        '#title' => $this->t($role),
        '#default_value' => $config->get('login_redirect.' . $key),
      );
    }

    // default path
    $form['default'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default'),
      '#default_value' => $config->get('login_redirect.default'),
    ];

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    // get the roles array from the form (couldn't figure out how to do this with $form_state)
    $roles = $_POST['roles'];

    // get the config object
    $config = $this->config('login_redirect.settings');

    // loop over each provided role and set the path in config
    foreach ($roles as $role => $path) {
      $config->set('login_redirect.' . $role, $path);
    }

    // set the default value
    $config->set('login_redirect.default', $form_state->getValue('default'));

    $config->save();

    drupal_set_message("Settings saved");

    return parent::submitForm($form, $form_state);
  }

  protected function getEditableConfigNames()
  {

    return [

      'login_redirect.settings',

    ];

  }

}