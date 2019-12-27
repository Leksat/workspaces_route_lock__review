<?php

namespace Drupal\workspaces_route_lock\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\workspaces\Entity\Workspace;

/**
 * Defines the Workspaces route lock entity.
 *
 * @ConfigEntityType(
 *   id = "workspaces_route_lock",
 *   label = @Translation("Workspaces route lock"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\workspaces_route_lock\WorkspacesRouteLockListBuilder",
 *     "form" = {
 *       "add" = "Drupal\workspaces_route_lock\Form\WorkspacesRouteLockForm",
 *       "edit" = "Drupal\workspaces_route_lock\Form\WorkspacesRouteLockForm",
 *       "delete" = "Drupal\workspaces_route_lock\Form\WorkspacesRouteLockDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\workspaces_route_lock\WorkspacesRouteLockHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "workspaces_route_lock",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/workflow/workspaces/route_lock/{workspaces_route_lock}",
 *     "add-form" = "/admin/config/workflow/workspaces/route_lock/add",
 *     "edit-form" = "/admin/config/workflow/workspaces/route_lock/{workspaces_route_lock}/edit",
 *     "delete-form" = "/admin/config/workflow/workspaces/route_lock/{workspaces_route_lock}/delete",
 *     "collection" = "/admin/config/workflow/workspaces/route_lock"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "workspaces",
 *   }
 * )
 */
class WorkspacesRouteLock extends ConfigEntityBase implements WorkspacesRouteLockInterface {

  /**
   * The Workspaces route lock ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The label.
   *
   * @var string
   */
  protected $label;

  /**
   * The list of allowed workspaces.
   *
   * @var string
   */
  protected $workspaces;

  /**
   * {@inheritDoc}
   */
  public function appliesToRoute(String $routeId) {
    $routeMask = $this->label();
    if ($routeId === $routeMask) {
      // The route matches directly.
      return TRUE;
    }

    // Escape the dots and turn '*' into '.*'.
    $pattern = str_replace(['.', '*'], ["\.", ".*"], $routeMask);
    return (bool) preg_match("/^$pattern$/", $routeId);
  }

  /**
   * {@inheritDoc}
   */
  public function workspaceIsAllowed(String $workspaceId) {
    return in_array($workspaceId, $this->getWorkspaces());
  }

  /**
   * {@inheritDoc}
   */
  public function getWorkspaceToSwitchTo() {
    foreach ($this->getWorkspaces() as $workspaceId) {
      $workspace = Workspace::load($workspaceId);
      if ($workspace) {
        return $workspace;
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getWorkspaces() {
    return $this->get('workspaces');
  }

  /**
   * {@inheritDoc}
   */
  public function getRoutePattern() {
    return $this->label();
  }

}
