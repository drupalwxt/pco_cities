<?php

namespace Drupal\Tests\pco_cities\Kernel;

use Drupal\Core\Extension\Extension;
use Drupal\KernelTests\KernelTestBase;
use Drupal\pco_cities\ComponentDiscovery;

/**
 * @group pco_cities
 *
 * @coversDefaultClass \Drupal\pco_cities\ComponentDiscovery
 */
class ComponentDiscoveryTest extends KernelTestBase {

  /**
   * The ComponentDiscovery under test.
   *
   * @var \Drupal\pco_cities\ComponentDiscovery
   */
  protected $discovery;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->discovery = new ComponentDiscovery(
      $this->container->get('app.root')
    );
  }

  /**
   * @covers ::getAll
   */
  public function testGetAll() {
    $components = $this->discovery->getAll();

    $this->assertInstanceOf(Extension::class, $components['pco_cities_core']);
    $this->assertInstanceOf(Extension::class, $components['pco_cities_ext']);
  }

  /**
   * @covers ::getMainComponents
   */
  public function testGetMainComponents() {
    $components = $this->discovery->getMainComponents();

    $this->assertInstanceOf(Extension::class, $components['pco_cities_core']);
  }

  /**
   * @covers ::getSubComponents
   */
  public function testGetSubComponents() {
    $components = $this->discovery->getSubComponents();

    $this->assertArrayNotHasKey('pco_cities_core', $components);
  }

}
