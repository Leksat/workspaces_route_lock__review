<?php

namespace Drupal\Tests\workspaces_route_lock\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\workspaces_route_lock\Entity\WorkspacesRouteLock;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group workspaces_route_lock
 */
class FunctionalIntegrationTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'toolbar',
    'workspaces_route_lock',
  ];

  /**
   * The default theme to use.
   *
   * @var string
   */
  protected $defaultTheme = 'classy';

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser([
      'access administration pages',
      'access toolbar',
      'administer site configuration',
      'administer workspaces',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Tests that the system routes are locked as needed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSystemRoutes() {
    // Lock the /admin page to stage.
    WorkspacesRouteLock::create([
      'id' => 'system_admin',
      'label' => 'system.admin',
      'workspaces' => ['stage'],
    ])->save();
    // Local all the paths that start with system.admin_s
    WorkspacesRouteLock::create([
      'id' => 'system_admin_config_s_',
      'label' => 'system.admin_config_s*',
      'workspaces' => ['stage'],
    ])->save();

    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertActiveWorkspace('Live');

    $this->drupalGet(Url::fromRoute('system.admin'));
    $this->assertActiveWorkspace('Stage');

    $this->drupalGet(Url::fromRoute('system.admin_config_search'));
    $this->assertActiveWorkspace('Stage');

    $this->drupalGet(Url::fromRoute('system.admin_config_system'));
    $this->assertActiveWorkspace('Stage');

    $this->drupalGet(Url::fromRoute('system.admin_config_regional'));
    $this->assertActiveWorkspace('Live');
  }

  /**
   * Asserts the active workspace name.
   *
   * @param String $name
   *   The name of the workspace as displayed in the toolbar.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function assertActiveWorkspace($name) {
    $this->assertSession()->statusCodeEquals(200);
    $link = $this->getSession()->getPage()->findLink('Switch workspace');
    $this->assertEquals($name, $link->getText(), 'The active workspaces matches the expected value ' . $name . '.');
  }

}
