<?php

namespace Drupal\submission_form_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;

class SubmissionFormModuleController extends ControllerBase {

  public function submissionSuccessPage($challenge) {

    $challenge_slug = \Drupal::request()->get('challenge');
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
    }
    else {
      throw new NotFoundHttpException();
    }

    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    $page['#theme'] = 'submission_form_module_page_theme';
    $page['#attached']['library'][] = 'submission_form_module/submission-form';

    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);

    $page['#submission_success'] = TRUE;
    $page['#submission_email'] = \Drupal::request()->get('email');

    return $page;
  }

  public function submissionFormPage($challenge) {
    $challenge_slug = \Drupal::request()->get('challenge');
    $submission_error = \Drupal::request()->get('error');

    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
    }
    else {
      throw new NotFoundHttpException();
    }

    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    $form = \Drupal::formBuilder()->getForm('Drupal\submission_form_module\Form\SubmissionForm');

    // Wrap the theme with WET4 validation tag.
    $form['#prefix'] = '<div class="wb-frmvld">';
    $form['#suffix'] = '</div>';

    $form['#theme'] = 'submission_form_module_page_theme';
    $form['#attached']['library'][] = 'submission_form_module/submission-form';

    $form['#challenge_name'] = $node->title->value;
    $form['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $form['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);

    $form['#submission_error'] = $submission_error;

    return $form;
  }

}
