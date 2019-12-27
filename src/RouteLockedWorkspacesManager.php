<?php

namespace Drupal\workspaces_route_lock;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\workspaces\Entity\Workspace;
use Drupal\workspaces\WorkspaceInterface;
use Drupal\workspaces\WorkspaceManagerInterface;
use Drupal\workspaces_route_lock\Entity\WorkspacesRouteLock;

class RouteLockedWorkspacesManager implements WorkspaceManagerInterface {

  /**
   * The underlying manager service.
   *
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $workspacesManager;

  /**
   * The current route match.
   *
   * @var RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The cache backend.
   *
   * @var CacheBackendInterface
   */
  protected $cache;

  /**
   * WorkspacesModerationManager constructor.
   *
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspaceManager
   *   The underlying manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $currentRouteMatch
   *   The current route match.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   */
  public function __construct(WorkspaceManagerInterface $workspaceManager, RouteMatchInterface $currentRouteMatch, CacheBackendInterface $cache) {
    $this->workspacesManager = $workspaceManager;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->cache = $cache;
  }

  /**
   * @inheritDoc
   */
  public function getActiveWorkspace() {
    $activeWorkspace = $this->workspacesManager->getActiveWorkspace();
    $routeName = $this->currentRouteMatch->getRouteName();

    if ($routeName) {
      $cid = "workspaces_route_lock:$routeName";
      $cacheItem = $this->cache->get($cid);

      if (!$cacheItem) {
        /** @var \Drupal\workspaces_route_lock\Entity\WorkspacesRouteLockInterface $routeLock */
        foreach (WorkspacesRouteLock::loadMultiple() as $routeLock) {
          if ($routeLock->appliesToRoute($routeName)) {
            if (!$routeLock->workspaceIsAllowed($this->getWorkspaceId($activeWorkspace))) {
              $activeWorkspace = $routeLock->getWorkspaceToSwitchTo();
              break;
            }
          }
        }
        $this->cache->set(
          $cid,
          $this->getWorkspaceId($activeWorkspace),
          Cache::PERMANENT,
          ['config:workspaces_route_lock_list']
        );
      } elseif ($cacheItem->data !== $activeWorkspace->id()) {
        $activeWorkspace = $cacheItem->data;
        if ($activeWorkspace) {
          $activeWorkspace = Workspace::load($activeWorkspace);
        }
      }
    }

    return $activeWorkspace;
  }

  /**
   * @inheritDoc
   */
  public function shouldAlterOperations(EntityTypeInterface $entity_type) {
    return $this->isEntityTypeSupported($entity_type) && !$this->getActiveWorkspace()->isDefaultWorkspace();
  }
  /**
   * @inheritDoc
   */
  public function isEntityTypeSupported(EntityTypeInterface $entity_type) {
    return $this->workspacesManager->isEntityTypeSupported($entity_type);
  }

  /**
   * @inheritDoc
   */
  public function getSupportedEntityTypes() {
    return $this->workspacesManager->getSupportedEntityTypes();
  }

  /**
   * @inheritDoc
   */
  public function hasActiveWorkspace() {
    return $this->workspacesManager->hasActiveWorkspace();
  }

  /**
   * @inheritDoc
   */
  public function setActiveWorkspace(WorkspaceInterface $workspace) {
    return $this->workspacesManager->setActiveWorkspace($workspace);
  }

  /**
   * @inheritDoc
   */
  public function switchToLive() {
    return $this->workspacesManager->switchToLive();
  }

  /**
   * @inheritDoc
   */
  public function executeInWorkspace($workspace_id, callable $function) {
    return $this->workspacesManager->executeInWorkspace($workspace_id, $function);
  }

  /**
   * @inheritDoc
   */
  public function executeOutsideWorkspace(callable $function) {
    return $this->workspacesManager->executeOutsideWorkspace($function);
  }

  /**
   * @inheritDoc
   */
  public function purgeDeletedWorkspacesBatch() {
    return $this->workspacesManager->purgeDeletedWorkspacesBatch();
  }

  /**
   * Returns the workspace id if the given argument is not falsy.
   *
   * @param mixed $workspace
   *   The result of getActiveWorkspace. Either an object or false.
   *
   * @return mixed
   */
  protected function getWorkspaceId($workspace) {
    if ($workspace) {
      return $workspace->id();
    } else {
      return $workspace;
    }
  }

}
