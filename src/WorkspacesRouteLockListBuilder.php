<?php

namespace Drupal\workspaces_route_lock;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\workspaces_route_lock\Entity\WorkspacesRouteLock;

/**
 * Provides a listing of Workspaces route lock entities.
 */
class WorkspacesRouteLockListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Route pattern');
    $header['workspaces'] = $this->t('Workspaces');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    assert($entity instanceof WorkspacesRouteLock);
    $row['label'] = $entity->label();
    $row['workspaces'] = implode(', ', $entity->getWorkspaces());
    return $row + parent::buildRow($entity);
  }

}
