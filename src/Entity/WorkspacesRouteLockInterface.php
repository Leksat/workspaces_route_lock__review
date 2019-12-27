<?php

namespace Drupal\workspaces_route_lock\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Workspaces route lock entities.
 */
interface WorkspacesRouteLockInterface extends ConfigEntityInterface {

  /**
   * Checks if this route lock item applies to the given route id.
   *
   * @param string $routeId
   *   The route id.
   *
   * @return bool
   */
  public function appliesToRoute(String $routeId);

  /**
   * Tells if a given workspace is allowed on this route.
   *
   * @param String $workspaceId
   *   The id of the workspace.
   *
   * @return bool
   */
  public function workspaceIsAllowed(String $workspaceId);

  /**
   * Returns the first workspace from the available workspaces list.
   *
   * @return \Drupal\workspaces\WorkspaceInterface
   */
  public function getWorkspaceToSwitchTo();

  /**
   * Returns the ids of allowed workspaces.
   *
   * @return String[]
   */
  public function getWorkspaces();

  /**
   * Returns the route name or pattern.
   *
   * @return String
   */
  public function getRoutePattern();

}
