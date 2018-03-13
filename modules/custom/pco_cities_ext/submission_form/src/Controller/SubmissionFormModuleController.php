<?php

namespace Drupal\submission_form_module\Controller;

use Drupal\Core\Controller\ControllerBase;

class SubmissionFormModuleController extends ControllerBase {

  public function submission_success_page($challenge) {

    $challenge_slug = \Drupal::request()->get('challenge');
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
    } else {
      throw new NotFoundHttpException();
    }

    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    /* Code to render the node menu, inject into our own template string */
    /*
      $node = \Drupal::entityManager()->getStorage('node')->load($nodeid);
      $view_builder = \Drupal::entityManager()->getViewBuilder('node');
      $renderarray = $view_builder->view($node, 'full');
      $html = \Drupal::service('renderer')->renderRoot($renderarray);
    */


    $page['#theme'] = 'submission_form_module_page_theme';
    $page['#attached']['library'][] = 'submission_form_module/submission-form';

    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);

    $page['#submission_success'] = true;
    $page['#submission_email'] = \Drupal::request()->get('email');

    return $page;

    $message = $this->t('This is the success page. Email was sent.', [
      '%from' => $from,
      '%to' => $to,
    ]);

    return ['#markup' => $message];
  }

  public function submission_form_page($challenge) {
    $challenge_slug = \Drupal::request()->get('challenge');
    $submission_error = \Drupal::request()->get('error');

    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
    } else {
      throw new NotFoundHttpException();
    }

    //$submission_email = $node->get('field_submission_email')->getValue();

    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    /* Code to render the node menu, inject into our own template string. May or may not be needed in future. */
    /*
      $node = \Drupal::entityManager()->getStorage('node')->load($nodeid);
      $view_builder = \Drupal::entityManager()->getViewBuilder('node');
      $renderarray = $view_builder->view($node, 'full');
      $html = \Drupal::service('renderer')->renderRoot($renderarray);
    */

    $form = \Drupal::formBuilder()->getForm('Drupal\submission_form_module\Form\SubmissionForm');

    //Wrap the theme in WET4 validation
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
