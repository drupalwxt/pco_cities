<?php

namespace Drupal\submission_form_module\Form;

use Mailgun\Mailgun;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SubmissionForm extends FormBase {

  public function getFormId() {
    return 'submission_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('submission_form_module.settings');

    //Title and Summary
    $form['title'] = [
      '#type' => 'textfield',
      '#id' => 'title',
      '#attributes' => array('class' => array('full-width'), 'drupal-field-name' => array('Title')),
      '#required' => true
    ];

    $form['summary'] = [
      '#type' => 'textarea',
      '#attributes' => array('class' => array('full-width'), 'drupal-field-name' => array('Summary'), 'data-rule-maxlength' => array('1200')),
      '#required' => true,
      '#maxlength' => 1200
    ];

    $form['proposal'] = [
      '#type' => 'managed_file',
      '#size' => 20,
      '#multiple' => TRUE,
      '#description' => t('.docx and .pdf formats only'),
      '#upload_location' => 'public://proposals/',
      '#required' => true,
      '#upload_validators' => [
        'file_validate_size' => array(25 * 1024 * 1024),
        'file_validate_extensions' => array('pdf docx'),
      ]
    ];

    $form['proposal_image'] = [
      '#type' => 'managed_file',
      '#size' => 20,
      '#description' => t('.jpg and .png formats only'),
      '#upload_location' => 'public://proposals/images',
      '#required' => true,
      '#upload_validators' => [
        'file_validate_size' => array(25 * 1024 * 1024),
        'file_validate_image_resolution' => array('1920x1080'),
        'file_validate_extensions' => array('gif png jpg jpeg'),
      ]
    ];

    $form['link'] = [
      '#type' => 'textfield',
      '#attributes' => array('class' => array('full-width'), 'drupal-field-name' => array('Video Link')),
    ];

    $form['primary_contact_name'] = [
      '#type' => 'textfield',
      '#attributes' => array('class' => array('full-width'), 'drupal-field-name' => array('Full Name')),
      '#required' => true
    ];

    $form['primary_contact_email'] = [
      '#type' => 'textfield',
      '#attributes' => array('class' => array('full-width'), 'drupal-field-name' => array('Email Address')),
      '#required' => true
    ];

    $form['terms_agreement'] = [
      '#type' => 'checkbox',
      '#required' => true,
      '#attributes' => array('drupal-field-name' => array('Terms & Conditions')),
      '#theme_wrappers'   => array(), //Removes the wrapper
      '#prefix'           => '<div class="form-item form-group">',
      '#suffix'           => '<label> I have read the <a href="/en/terms-and-conditions">Terms and Conditions</a> and the <a href="/en/privacy">Privacy Policy</a> and agree to both.</label></div>',
    ];

    $form['guidelines_agreement'] = [
      '#type' => 'checkbox',
      '#required' => true,
      '#attributes' => array('drupal-field-name' => array('Guidelines')),
      '#theme_wrappers'   => array(), //Removes the wrapper
      '#prefix'           => '<div class="form-item form-group">',
      '#suffix'           => '<label> I have read the <a href="#">Submission Guidelines.</a></label></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit My Application >'),
      '#attributes' => array('class' => array('mrgn-tp-xl', 'btn-submit'))
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $challenge_slug = \Drupal::request()->get('challenge'); //Current Challenge
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    //Grabs content specific to this challenge node.
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
    } else {
      throw new NotFoundHttpException();
    }

    $file_arr = array(); //File array

    //Loop through multiple file upload and grab download links.
    foreach($form_state->getValue('proposal') as $file) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file);
      $file_uri = file_create_url($file->get('uri')->value);
      array_push($file_arr, array('name' => $file->get('filename')->value, 'link' => $file_uri));
    }

    //Also grab download link for the image
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('proposal_image')[0]);
    $file_uri = file_create_url($file->get('uri')->value);
    array_push($file_arr, array('name' => $file->get('filename')->value, 'link' => $file_uri));

    //Formulate a template string to be thrown into email template for download links
    $template_string = '';
    foreach($file_arr as $link) {
      $template_string = $template_string . '<a href="' . $link['link'] . '">' . $link['name'] . '</a><br/>';
      array_push($file_arr, $file_uri);
    }


     //Variables grabbed from form submission state.
     $variables = [
      'challenge' => $node->title->value,
      'title' => $form_state->getValue('title'),
      'summary' => $form_state->getValue('summary'),
      'primary_contact_name' => $form_state->getValue('primary_contact_name'),
      'primary_contact_email' => $form_state->getValue('primary_contact_email'),
      'link' => $form_state->getValue('link'),
      'file_links' => $template_string,
      'email_contents' => $node->get('field_challenge_email_contents')->getValue()[0]['value']
    ];

    //Generate the template
    $template = file_get_contents(drupal_get_path('module', 'submission_form_module') . '/templates/submission-email.html');
    $template = $this->renderTemplate($template, $variables);

    //Instantiate Mailgun with API Key and sending domain.
    $mailgun = Mailgun::create('key-5q8rkuph2j8fey5owt5kcaydbll9bzb2');
    $domain = 'mailgun.cds-snc.ca';

    $send_data = [
      'from'    => 'contact@mailgun.cds-snc.ca',
      'to'      => $node->get('field_challenge_submission_email')->getValue()[0]['value'], //Email specified from within the CMS
      'subject' => 'Challenge Submission - ' . $variables['challenge'],
      'html'    => $template
    ];

    // Send the email to challenge owners, and then capture the response.
    $challenge_submission_email = $mailgun->sendMessage($domain, $send_data);

    if ($challenge_submission_email->http_response_code === 200) {

      $send_data['to'] = $variables['primary_contact_email']; //Send the same email to the user as confirmation.

      //Send the email to the user as confirmation, capture the response.
      $user_confirmation_email= $mailgun->sendMessage($domain, $send_data);

      if ($challenge_submission_email->http_response_code === 200) {
        //Redirect to success page. Which should be another module, eventually.
        $form_state->setRedirect('submission_form_module.submission_success_page', array('challenge' => $challenge_slug, 'email' => $variables['primary_contact_email']));
      }
      else {
        //Return user to module submission page, display error.
        $form_state->setRedirect('submission_form_module.submission_form_page', array('challenge' => $challenge_slug, 'error' => TRUE));
      }
    }
    else {
      //Return user to module submission page, display error.
      $form_state->setRedirect('submission_form_module.submission_form_page', array('challenge' => $challenge_slug, 'error' => TRUE));
    }
  }

  private function renderTemplate(string $templateP, array $variablesP)
  {
    $template = $templateP;

    foreach($variablesP as $key => $value)
    {
        $template = str_replace('{{ '.$key.' }}', $value, $template);
    }

    return $template;
  }

  protected function getEditableConfigNames() {
    return ['submission_form_module.settings'];
  }
}
