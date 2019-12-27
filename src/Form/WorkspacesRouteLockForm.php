<?php

namespace Drupal\workspaces_route_lock\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workspaces\Entity\Workspace;

/**
 * Class WorkspacesRouteLockForm.
 */
class WorkspacesRouteLockForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $routeLockEntity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route id'),
      '#maxlength' => 255,
      '#default_value' => $routeLockEntity->label(),
      '#description' => $this->t('The routes that should be locked. * can be used as w wildcard. Examples: <em>entity.taxonomy_term.canonical</em>, <em>entity.taxonomy_term.*</em>'),
      '#element_validate' => ['::validateRoute'],
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $routeLockEntity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\workspaces_route_lock\Entity\WorkspacesRouteLock::load',
      ],
      '#disabled' => !$routeLockEntity->isNew(),
    ];

    $workspaces = $routeLockEntity->get('workspaces');
    $form['workspaces'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Allowed workspaces'),
      '#multiple' => TRUE,
      '#tags' => TRUE,
      '#default_value' => empty($workspaces)
        ? [] : Workspace::loadMultiple($workspaces),
      '#target_type' => 'workspace',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $routeLockEntity = $this->entity;
    $routeLockEntity->set('workspaces', array_map(function ($item) {
      return $item['target_id'];
    }, $routeLockEntity->get('workspaces')));
    $status = $routeLockEntity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the workspace lock for %label.', [
          '%label' => $routeLockEntity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the workspace lock for %label.', [
          '%label' => $routeLockEntity->label(),
        ]));
    }
    $form_state->setRedirectUrl($routeLockEntity->toUrl('collection'));
  }

  /**
   * Validate callback for the route pattern field.
   *
   * @param array $element
   *   A text element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state object.
   */
  public function validateRoute(array $element, FormStateInterface $formState) {
    /** @var \Drupal\workspaces_route_lock\Entity\WorkspacesRouteLockInterface $routeLockEntity */
    $routeLockEntity = $this->entity;
    /** @var \Drupal\Core\Routing\RouteProviderInterface $routeProvider */
    $routeProvider = \Drupal::service('router.route_provider');
    $routes = $routeProvider->getAllRoutes();
    foreach ($routes as $routeId => $route) {
      if ($routeLockEntity->appliesToRoute($routeId)) {
        // At least one route applies, so the pattern is valid.
        return;
      }
    }

    // None of the routes matched.
    $formState->setError($element, 'None of the routes in the system matches the given id pattern.');
  }

}
