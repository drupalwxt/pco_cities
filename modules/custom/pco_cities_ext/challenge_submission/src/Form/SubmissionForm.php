<?php

namespace Drupal\challenge_submission\Form;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Mailgun\Mailgun;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubmissionForm extends FormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Constructs a SubmissionFormModuleController object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Database\Connection $db
   */
  public function __construct(AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, Connection $db) {
    $this->aliasManager = $aliasManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.alias_manager'),
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'submission_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Title and Summary.
    $form['friendly_url'] = [
      '#type' => 'hidden',
      '#id' => 'friendly-url',
    ];

    // Title and Summary.
    $form['title'] = [
      '#type' => 'textfield',
      '#id' => 'title',
      '#attributes' => ['class' => ['full-width'], 'drupal-field-name' => ['Title']],
      '#required' => TRUE,
      '#title' => 'Title'
    ];

    $form['summary'] = [
      '#type' => 'textarea',
      '#attributes' => [
        'class' => [
          'full-width',
        ], 'drupal-field-name' => [
          'Summary',
        ], 'data-rule-maxlength' => [
          '1200',
        ],
      ],
      '#required' => TRUE,
      '#maxlength' => 1200,
      // Removes the wrapper.
      '#theme_wrappers'   => [],
      '#prefix'           => '<div class="form-item form-group">',
      '#suffix'           => '<div class="word-count"><span class="summary-word-count"></span>/150</div></div>',
      '#title' => 'Summary'
    ];

    $form['proposal'] = [
      '#type' => 'managed_file',
      '#size' => 20,
      '#multiple' => TRUE,
      '#description' => $this->t('.docx and .pdf formats only'),
      '#upload_location' => 'public://proposals/',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_size' => [25 * 1024 * 1024],
        'file_validate_extensions' => ['pdf docx'],
      ],
      '#title' => 'Primary Files'
    ];

    $form['proposal_image'] = [
      '#type' => 'managed_file',
      '#size' => 20,
      '#description' => $this->t('.jpg and .png formats only'),
      '#upload_location' => 'public://proposals/images',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_size' => [25 * 1024 * 1024],
        'file_validate_image_resolution' => ['1920x1080'],
        'file_validate_extensions' => ['gif png jpg jpeg'],
      ],
      '#title' => 'Proposal Image'
    ];

    $form['link'] = [
      '#type' => 'textfield',
      '#attributes' => ['class' => ['full-width'], 'drupal-field-name' => ['Video Link']],
      '#title' => 'Video Link'
    ];

    $form['primary_contact_name'] = [
      '#type' => 'textfield',
      '#attributes' => ['class' => ['full-width'], 'drupal-field-name' => ['Full Name']],
      '#required' => TRUE,
      '#title' => 'Primary Contact Name'
    ];

    $form['primary_contact_email'] = [
      '#type' => 'textfield',
      '#attributes' => ['class' => ['full-width'], 'drupal-field-name' => ['Email Address']],
      '#required' => TRUE,
      '#title' => 'Primary Contact Email'
    ];

    $form['terms_agreement'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#attributes' => ['drupal-field-name' => ['Terms & Conditions']],
      // Removes the wrapper.
      '#title' => 'Terms Agreement',
      '#theme_wrappers'   => [],
      '#prefix'           => '<div class="form-item form-group">',
      '#suffix'           => '<label> I have read the <a href="/en/terms-and-conditions">Terms and Conditions</a> and the <a href="/en/privacy">Privacy Policy</a> and agree to both.</label></div>',
    ];

    $form['guidelines_agreement'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#attributes' => ['drupal-field-name' => ['Guidelines']],
      // Removes the wrapper.
      '#title' => 'Guidelines Agreement',
      '#theme_wrappers'   => [],
      '#prefix'           => '<div class="form-item form-group">',
      '#suffix'           => '<label> I have read the <a href="#">Submission Guidelines.</a></label></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit My Application >'),
      '#attributes' => ['class' => ['mrgn-tp-xl', 'btn-submit']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $challenge_slug = $form_state->getValue('friendly_url');

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $defaultLang = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $nids = \Drupal::entityQuery('node')->condition('type', 'challenge')->execute();
    $nodes = Node::loadMultiple($nids);
    $node = NULL;

    foreach ($nodes as $item) {
      if ($item->get('field_friendly_url')->getValue()) {
        $url = $item->get('field_friendly_url')->getValue()[0]['value'];

        // Check for french translation.
        if ($item->getTranslation($language)->get('field_friendly_url')->getValue()) {
          $url_french = $item->getTranslation($language)->get('field_friendly_url')->getValue()[0]['value'];
        }

        if ($url == $challenge_slug) {
          $node = $item;
          break;
        }

        if ($url_french == $challenge_slug) {
          $node = $item;
          break;
        }
      }
    }

    // If no matching node, then we throw an exception.
    if (!$node) {
      throw new NotFoundHttpException();
    }

    // File array.
    $file_arr = [];

    // Loop through multiple file upload and grab download links.
    foreach ($form_state->getValue('proposal') as $file) {
      $file = $this->entityTypeManager->getStorage('file')->load($file);
      $file_uri = file_create_url($file->get('uri')->value);
      array_push($file_arr, ['name' => $file->get('filename')->value, 'link' => $file_uri]);
    }

    // Also grab download link for the image.
    $file = $this->entityTypeManager->getStorage('file')->load($form_state->getValue('proposal_image')[0]);
    $file_uri = file_create_url($file->get('uri')->value);
    array_push($file_arr, ['name' => $file->get('filename')->value, 'link' => $file_uri]);

    // Template string to be thrown into email template for download links.
    $template_string = '';
    foreach ($file_arr as $link) {
      $template_string = $template_string . '<a href="' . $link['link'] . '">' . $link['name'] . '</a><br/>';
      array_push($file_arr, $file_uri);
    }

    // Variables grabbed from form submission state.
    $variables = [
      'challenge' => $node->title->value,
      'title' => $form_state->getValue('title'),
      'summary' => $form_state->getValue('summary'),
      'primary_contact_name' => $form_state->getValue('primary_contact_name'),
      'primary_contact_email' => $form_state->getValue('primary_contact_email'),
      'link' => $form_state->getValue('link'),
      'file_links' => $template_string,
      'email_contents' => $node->get('field_challenge_email_contents')->getValue()[0]['value'] ?? '',
    ];

    $this->saveToAuditLog($variables);

    // Generate the template.
    $template = file_get_contents(drupal_get_path('module', 'challenge_submission') . '/templates/submission-email.html');
    $template = $this->renderTemplate($template, $variables);

    $config = Drupal::config('challenge_submission.settings');

    // Instantiate Mailgun with API Key and sending domain.
    // Mailgun::create('key-5q8rkuph2j8fey5owt5kcaydbll9bzb2');.
    $mailgun = Mailgun::create($config->get('challenge_submission.mailgun_key'));
    // 'mailgun.cds-snc.ca';.
    $domain = $config->get('challenge_submission.mailgun_domain');

    $send_data = [
      'from'    => 'contact@mailgun.cds-snc.ca',
    // Email specified from within the CMS.
      'to'      => $node->get('field_challenge_submission_email')->getValue()[0]['value'],
      'subject' => 'Challenge Submission - ' . $variables['challenge'],
      'html'    => $template,
    ];

    // Send the email to challenge owners, and then capture the response.
    $challenge_submission_email = $mailgun->sendMessage($domain, $send_data);

    if ($challenge_submission_email->http_response_code === 200) {

      // Send the same email to the user as confirmation.
      $send_data['to'] = $variables['primary_contact_email'];

      // Send the email to the user as confirmation, capture the response.
      $mailgun->sendMessage($domain, $send_data);

      if ($challenge_submission_email->http_response_code === 200) {
        // Redirect to success page. Which should be another module, eventually.
        $form_state->setRedirect('challenge_submission.submission_success_page', ['challenge' => $challenge_slug, 'email' => $variables['primary_contact_email']]);
      }
      else {
        // Return user to module submission page, display error.
        $form_state->setRedirect('challenge_submission.submission_form_page', ['challenge' => $challenge_slug, 'error' => TRUE]);
      }
    }
    else {
      // Return user to module submission page, display error.
      $form_state->setRedirect('challenge_submission.submission_form_page', ['challenge' => $challenge_slug, 'error' => TRUE]);
    }
  }

  private function renderTemplate(string $templateP, array $variablesP) {
    $template = $templateP;

    foreach ($variablesP as $key => $value) {
      $template = str_replace('{{ ' . $key . ' }}', $value, $template);
    }

    return $template;
  }

  protected function getEditableConfigNames() {
    return ['challenge_submission.settings'];
  }

  protected function saveToAuditLog(array $data) {
    $fields = [
      'challenge' => $data['challenge'],
      'title' => $data['title'],
      'summary' => $data['summary'],
      'primary_contact_name' => $data['primary_contact_name'],
      'primary_contact_email' => $data['primary_contact_email'],
      'link' => $data['link'],
      'file_links' => $data['file_links'],
      'submitted_at' => strtotime('now'),
    ];

    $this->db->insert('challenge_submission')->fields($fields)->execute();
  }

}
