<?php

namespace Drupal\challenge_page_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChallengePageModuleController extends ControllerBase {

  public function challenge_page($challenge) {

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

    // User variables.
    $user = \Drupal::currentUser();
    $page['#logged_in'] = $user->isAuthenticated();

    // Create the Menu.
    $page['#challenge_menu_array'] = $this->generateMenuBar($node);

    // Page System Variables.
    $page['#theme'] = 'challenge_page_module_page_theme';
    $page['#attached']['library'][] = 'challenge_page_module/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge_slug . '/home';
    $page['#challenge_root'] = '/challenges/' . $challenge_slug;

    $page['#challenge_node'] = $path;

    // Page Content.
    $page['#challenge_description'] = $node->get('field_challenge_description')->getValue()[0];
    $page['#challenge_details'] = $node->get('field_challenge_details_block')->getValue();

    return $page;
  }

  public function challenge_subpage($challenge, $url) {

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

    // Create the Menu.
    $page['#challenge_menu_array'] = $this->generateMenuBar($node);

    // Page System Variables.
    $page['#theme'] = 'challenge_subpage_module_page_theme';
    $page['#attached']['library'][] = 'challenge_page_module/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge_slug . '/home';
    $page['#challenge_root'] = '/challenges/' . $challenge_slug;
    $page['#challenge_node'] = $path;

    // User variables.
    $user = \Drupal::currentUser();
    $page['#logged_in'] = $user->isAuthenticated();

    // Find out which page we're on.
    if ($this->validateFriendlyUrlMatch($node->get('field_challenge_subpage_title_1')->getValue()[0]['value'], $url)) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_1')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_1')->getValue()[0];
    }
    elseif ($this->validateFriendlyUrlMatch($node->get('field_challenge_subpage_title_2')->getValue()[0]['value'], $url)) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_2')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_2')->getValue()[0];
    }
    elseif ($this->validateFriendlyUrlMatch($node->get('field_challenge_subpage_title_3')->getValue()[0]['value'], $url)) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_3')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_3')->getValue()[0];
    }
    elseif ($this->validateFriendlyUrlMatch($node->get('field_challenge_subpage_title_4')->getValue()[0]['value'], $url)) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_4')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_4')->getValue()[0];
    }
    else {
      throw new NotFoundHttpException();
    }

    return $page;
  }

  public function challenge_news_page($challenge) {
    $challenge_slug = \Drupal::request()->get('challenge');
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
    }
    else {
      throw new NotFoundHttpException();
    }

    $nids = \Drupal::entityQuery('node')->condition('type', 'challenge_news')->execute();
    $nodes = Node::loadMultiple($nids);
    $news_array = [];

    foreach ($nodes as $item) {
      $target_id = $item->get('field_challenge')->getValue()[0]['target_id'];

      if ($target_id == $node->id()) {
        array_push($news_array, [
          'title' => $item->get('title')->getValue()[0]['value'],
          'header' => $item->get('field_type')->getValue()[0]['value'],
          'body' => $item->get('body')->getValue()[0],
          'sidebar' => $item->get('field_sidebar')->getValue()[0],
        ]);
      }
    }

    // User variables.
    $user = \Drupal::currentUser();
    $page['#logged_in'] = $user->isAuthenticated();

    // Create the Menu.
    $page['#challenge_menu_array'] = $this->generateMenuBar($node);

    // Articles.
    $page['#news'] = $news_array;

    // Page System Variables.
    $page['#theme'] = 'challenge_news_module_page_theme';
    $page['#attached']['library'][] = 'challenge_page_module/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge_slug . '/home';
    $page['#challenge_root'] = '/challenges/' . $challenge_slug;
    $page['#challenge_node'] = $path;

    return $page;
  }

  private function validateFriendlyUrlMatch($unfriendlyUrl, $friendlyUrl) {
    if (preg_replace('/\W+/', '-', strtolower($unfriendlyUrl)) == $friendlyUrl) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private function generateMenuBar($node) {
    $menu = [];

    if ($node->get('field_challenge_subpage_title_1')->getValue()) {
      $friendly_url = preg_replace('/\W+/', '-', strtolower($node->get('field_challenge_subpage_title_1')->getValue()[0]['value']));
      array_push($menu, [
        'title' => $node->get('field_challenge_subpage_title_1')->getValue()[0]['value'],
        'url' => $friendly_url,
      ]);
    }
    if ($node->get('field_challenge_subpage_title_2')->getValue()) {
      $friendly_url = preg_replace('/\W+/', '-', strtolower($node->get('field_challenge_subpage_title_2')->getValue()[0]['value']));
      array_push($menu, [
        'title' => $node->get('field_challenge_subpage_title_2')->getValue()[0]['value'],
        'url' => $friendly_url,
      ]);
    }
    if ($node->get('field_challenge_subpage_title_3')->getValue()) {
      $friendly_url = preg_replace('/\W+/', '-', strtolower($node->get('field_challenge_subpage_title_3')->getValue()[0]['value']));
      array_push($menu, [
        'title' => $node->get('field_challenge_subpage_title_3')->getValue()[0]['value'],
        'url' => $friendly_url,
      ]);
    }
    if ($node->get('field_challenge_subpage_title_4')->getValue()) {
      $friendly_url = preg_replace('/\W+/', '-', strtolower($node->get('field_challenge_subpage_title_4')->getValue()[0]['value']));
      array_push($menu, [
        'title' => $node->get('field_challenge_subpage_title_4')->getValue()[0]['value'],
        'url' => $friendly_url,
      ]);
    }

    return $menu;
  }

}
