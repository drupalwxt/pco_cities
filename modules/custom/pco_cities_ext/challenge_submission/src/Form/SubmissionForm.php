<?php

namespace Drupal\challenge_submission\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubmissionForm extends FormBase
{
  public function getFormId()
  {
    return 'submission_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $challenge_slug = \Drupal::request()->get('challenge');
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
    } else {
      throw new NotFoundHttpException();
    }

    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    $form['challenge'] = [
      '#type' => 'hidden',
      '#default_value' => \Drupal::request()->get('challenge'),
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project Title'),
      '#required' => TRUE,
    ];

    $form['summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Project Summary'),
      '#value' => ''
    ];

    $form['primary_contact_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full name'),
      '#prefix' => '<h3>' . $this->t('Team Captain') . '</h3><p>' . $this->t('Your primary point of contact') . '</p>'
    ];

    $form['primary_contact_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address')
    ];

    $form['proposal'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload a file'),
      '#prefix' => '<h3>' . $this->t('Project Proposal') . '</h3><p>' . $this->t('We only accept PDFs and Word documents.') . '</p>'
    ];

    $form['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link to video'),
    ];

    $form['agree'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I Agree'),
      '#prefix' => '<h3>' . $this->t('Terms & Conditions') . '</h3><p>+ privacy information goes here.</p>'
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit my proposal')
    ];


    // $image_field = $node->get('field_challenge_image')->getValue();

return $form;
    return [
      '#theme' => 'challenge-submission',
      '#form' => $form,
      '#challenge' => [
        'title' => $node->title->value,
        'department' => $node->get('field_challenge_department')->getValue()[0],
        'image' => file_create_url($node->field_challenge_image->entity->uri->value),
        'node' => $node
      ]
    ];

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // var_dump("Huzzah");exit;
    $fields = [
      'challenge' => $form_state->getValue('challenge'),
      'title' => $form_state->getValue('title'),
      'summary' => $form_state->getValue('summary'),
      'primary_contact_name' => $form_state->getValue('primary_contact_name'),
      'primary_contact_email' => $form_state->getValue('primary_contact_email'),
      'fid' => 0,
      'link' => $form_state->getValue('link')
    ];

    \Drupal::database()->insert('challenge_submission')->fields($fields)->execute();

    drupal_set_message("Saved!");

    // \Drupal\Core\Form\drupal_set_message("Saved!");

    // return parent::submitForm($form, $form_state);
  }

}