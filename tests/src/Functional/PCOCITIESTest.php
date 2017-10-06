<?php

namespace Drupal\Tests\pco_cities\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests PCO CITIES installation profile expectations.
 *
 * @group pco_cities
 */
class PCOCITIESTest extends BrowserTestBase {

  /**
   * Installation profile.
   *
   * @var string
   */
  protected $profile = 'pco_cities';

  /**
   * Test for the login.
   */
  public function testOpenDataLogin() {
    // Create a user to check the login.
    $user = $this->createUser();

    // Log in our user.
    $this->drupalLogin($user);

    // Verify that logged in user can access the logout link.
    $this->drupalGet('user');

    $this->assertLinkByHref('/user/logout');
  }

}
