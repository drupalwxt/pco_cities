<?php

namespace Drupal\submission_form_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SubmissionAuditLogController extends ControllerBase {
  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Constructs a SubmissionFormModuleController object.
   *
   * @param \Drupal\Core\Database\Connection $db
   */
  public function __construct(Connection $db) {
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function display() {
    $header_table = [
      'id' => $this->t('Id'),
      'challenge' => $this->t('Challenge'),
      'title' => $this->t('Submission Title'),
      'summary' => $this->t('Summary'),
      'primary_contact_email' => $this->t('Contact Email'),
      'submitted_at' => $this->t('Submitted'),
    ];

    $query = $this->db->select('challenge_submission', 'm');
    $query->fields('m', [
      'csid',
      'challenge',
      'title',
      'summary',
      'primary_contact_name',
      'primary_contact_email',
      'submitted_at',
    ]);
    $results = $query->execute()->fetchAll();
    $rows = [];

    foreach ($results as $data) {
      // Print the data from table.
      $rows[] = [
        'id' => $data->csid,
        'challenge' => $data->challenge,
        'title' => $data->title,
      // $data->summary,.
        'summary' => mb_strimwidth($data->summary, 0, 200, "..."),
        'primary_contact_email' => $data->primary_contact_email,
        'submitted_at' => date('Y-m-d h:i:s', $data->submitted_at),
      ];
    }

    // Display data in site.
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => $this->t('No submissions found'),
    ];
    return $form;

  }

}
