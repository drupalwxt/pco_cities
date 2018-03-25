<?php

namespace Drupal\challenge_pages\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Controller\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChallengePageController extends ControllerBase {

  public function challenge_page($challenge) {
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

        if ($url == $challenge) {
          $node = $item;
          break;
        }

        if ($url_french == $challenge) {
          $node = $item;
          break;
        }
      }
    }

    // If no matching node, then we throw an exception.
    if (!$node) {
      throw new NotFoundHttpException();
    }

    if (!array_key_exists($language, $node->getTranslationLanguages())) {
      $node = $node->getTranslation($defaultLang);
    }
    else {
      $node = $node->getTranslation($language);
    }

    // User variables.
    $user = \Drupal::currentUser();
    $page['#logged_in'] = $user->isAuthenticated();

    // Create the Menu.
    $page['#challenge_menu_array'] = $this->generateMenuBar($node);

    // Page System Variables.
    $page['#theme'] = 'challenge_pages_page_theme';
    $page['#attached']['library'][] = 'challenge_pages/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge;
    $page['#challenge_root'] = '/challenges/' . $challenge;
    $page['#challenge_node'] = '/node/' . $node->id();

    // Page Content.
    $page['#challenge_description'] = $node->get('field_challenge_description')->getValue()[0];
    $page['#challenge_details'] = $node->get('field_challenge_details_block')->getValue();

    return $page;
  }

  public function challenge_subpage($challenge, $url) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $defaultLang = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $nids = \Drupal::entityQuery('node')->condition('type', 'challenge')->execute();
    $nodes = Node::loadMultiple($nids);
    $node = NULL;

    foreach ($nodes as $item) {
      if ($item->get('field_friendly_url')->getValue()) {
        $friendly_url = $item->get('field_friendly_url')->getValue()[0]['value'];

        // Check for french translation.
        if ($item->getTranslation($language)->get('field_friendly_url')->getValue()) {
          $url_french = $item->getTranslation($language)->get('field_friendly_url')->getValue()[0]['value'];
        }

        if ($friendly_url == $challenge) {
          $node = $item;
          break;
        }

        if ($url_french == $challenge) {
          $node = $item;
          break;
        }
      }
    }

    // If no matching node, then we throw an exception.
    if (!$node) {
      throw new NotFoundHttpException();
    }

    if (!array_key_exists($language, $node->getTranslationLanguages())) {
      $node = $node->getTranslation($defaultLang);
    }
    else {
      $node = $node->getTranslation($language);
    }

    // Create the Menu.
    $page['#challenge_menu_array'] = $this->generateMenuBar($node);

    // Page System Variables.
    $page['#theme'] = 'challenge_subpage_module_page_theme';
    $page['#attached']['library'][] = 'challenge_pages/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge;
    $page['#challenge_root'] = '/challenges/' . $challenge;
    $page['#challenge_node'] = '/node/' . $node->id();

    // User variables.
    $user = \Drupal::currentUser();
    $page['#logged_in'] = $user->isAuthenticated();

    // Find out which page we're on.
    if (($node->get('field_challeng_subpage_url_1')->getValue()[0]['value'] == $url) && ($node->get('field_challenge_subpage_enable_1')->getValue()[0]['value'])) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_1')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_1')->getValue()[0];
    }
    elseif (($node->get('field_challeng_subpage_url_2')->getValue()[0]['value'] == $url) && ($node->get('field_challenge_subpage_enable_2')->getValue()[0]['value'])) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_2')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_2')->getValue()[0];
    }
    elseif (($node->get('field_challeng_subpage_url_3')->getValue()[0]['value'] == $url) && ($node->get('field_challenge_subpage_enable_3')->getValue()[0]['value'])) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_3')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_3')->getValue()[0];
    }
    elseif (($node->get('field_challeng_subpage_url_4')->getValue()[0]['value'] == $url) && ($node->get('field_challenge_subpage_enable_4')->getValue()[0]['value'])) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_4')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_4')->getValue()[0];
    }
    elseif (($node->get('field_challeng_subpage_url_5')->getValue()[0]['value'] == $url) && ($node->get('field_challenge_subpage_enable_5')->getValue()[0]['value'])) {
      $page['#challenge_subpage_title'] = $node->get('field_challenge_subpage_title_5')->getValue()[0]['value'];
      $page['#challenge_subpage_body'] = $node->get('field_challenge_subpage_body_5')->getValue()[0];
    }
    else {
      throw new NotFoundHttpException();
    }

    return $page;
  }

  public function challenge_news_page($challenge) {
    $lang_code = \Drupal::service('language_manager')->getCurrentLanguage()->getId();

    $nids = \Drupal::entityQuery('node')->condition('type', 'challenge')->execute();
    $nodes = Node::loadMultiple($nids);
    $node = NULL;

    foreach ($nodes as $item) {
      if ($item->get('field_friendly_url')->getValue()) {
        $friendly_url = $item->get('field_friendly_url')->getValue()[0]['value'];

        if ($friendly_url == $challenge) {
          $node = $item;
        }
      }
    }

    // If no matching node, then we throw an exception.
    if (!$node) {
      throw new NotFoundHttpException();
    }

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $defaultLang = \Drupal::languageManager()->getDefaultLanguage()->getId();

    if (!array_key_exists($language, $node->getTranslationLanguages())) {
      $node = $node->getTranslation($defaultLang);
    }
    else {
      $node = $node->getTranslation($language);
    }

    $news_nids = \Drupal::entityQuery('node')->condition('type', 'challenge_news')->sort('created', 'DESC')->pager(5)->execute();
    $news_nodes = Node::loadMultiple($news_nids);
    $news_array = [];

    foreach ($news_nodes as $item) {
      if (!array_key_exists($language, $item->getTranslationLanguages())) {
        $item = $item->getTranslation($defaultLang);
      }
      else {
        $item = $item->getTranslation($language);
      }

      $target_id = $item->get('field_challenge')->getValue()[0]['target_id'];

      if ($target_id == $node->id()) {
        array_push($news_array, [
          'title' => $item->get('title')->getValue()[0]['value'],
          'header' => $item->get('field_type')->getValue()[0]['value'],
          'body' => $item->get('body')->getValue()[0],
          'sidebar' => $item->get('field_sidebar')->getValue()[0] ?? '',
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
    $page['#attached']['library'][] = 'challenge_pages/challenge-page';

    // Page Variables.
    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);
    $page['#challenge_home'] = '/challenges/' . $challenge;
    $page['#challenge_root'] = '/challenges/' . $challenge;
    $page['#challenge_node'] = '/node/' . $node->id();

    $page['#pager'] = [
      '#type' => 'pager',
    ];

    return $page;
  }

  private function generateMenuBar($node) {
    $menu = [];

    $challenge_path = \Drupal::request()->getRequestUri();

    if(substr($challenge_path, -1) == '/') {
      $challenge_path = substr($challenge_path, 0, -1);
    }

    list($challenge_url, $subpage_url) = explode($node->get('field_friendly_url')->getValue()[0]['value'], $challenge_path);

    $challenge_url = $challenge_url . $node->get('field_friendly_url')->getValue()[0]['value'];

    //Remove news path if it exists
    $challenge_path = str_replace('/news', "", $challenge_path);

    if($node->get('field_challenge_subpage_enable_1')->getValue()[0]['value']) {
      if($node->get('field_challenge_subpage_title_1')->getValue() && $node->get('field_challeng_subpage_url_1')->getValue()) {
        array_push($menu, [
          'title' => $node->get('field_challenge_subpage_title_1')->getValue()[0]['value'],
          'url' => $challenge_url . '/' . $node->get('field_challeng_subpage_url_1')->getValue()[0]['value']
        ]);
      }
    }
    if($node->get('field_challenge_subpage_enable_2')->getValue()[0]['value']) {
      if($node->get('field_challenge_subpage_title_2')->getValue() && $node->get('field_challeng_subpage_url_2')->getValue()) {
        array_push($menu, [
          'title' => $node->get('field_challenge_subpage_title_2')->getValue()[0]['value'],
          'url' => $challenge_url . '/' . $node->get('field_challeng_subpage_url_2')->getValue()[0]['value']
        ]);
      }
    }
    if($node->get('field_challenge_subpage_enable_3')->getValue()[0]['value']) {
      if($node->get('field_challenge_subpage_title_3')->getValue() && $node->get('field_challeng_subpage_url_3')->getValue()) {
        array_push($menu, [
          'title' => $node->get('field_challenge_subpage_title_3')->getValue()[0]['value'],
          'url' => $challenge_url . '/' . $node->get('field_challeng_subpage_url_3')->getValue()[0]['value']
        ]);
      }
    }
    if($node->get('field_challenge_subpage_enable_4')->getValue()[0]['value']) {
      if($node->get('field_challenge_subpage_title_4')->getValue() && $node->get('field_challeng_subpage_url_4')->getValue()) {
        array_push($menu, [
          'title' => $node->get('field_challenge_subpage_title_4')->getValue()[0]['value'],
          'url' => $challenge_url . '/' . $node->get('field_challeng_subpage_url_4')->getValue()[0]['value']
        ]);
      }
    }
    if($node->get('field_challenge_subpage_enable_5')->getValue()[0]['value']) {
      if($node->get('field_challenge_subpage_title_5')->getValue() && $node->get('field_challeng_subpage_url_5')->getValue()) {
        array_push($menu, [
          'title' => $node->get('field_challenge_subpage_title_5')->getValue()[0]['value'],
          'url' => $challenge_url . '/' . $node->get('field_challeng_subpage_url_5')->getValue()[0]['value']
        ]);
      }
    }

    return $menu;
  }

}
