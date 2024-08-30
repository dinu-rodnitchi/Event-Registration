<?php

namespace Drupal\event_registration;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides the Event Registrtion service.
 */
class EventRegistrationService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the CommentBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns the registrations count.
   *
   * @return int
   *   The registration count.
   */
  public function registrationsCount() {

    /** @var \Drupal\node\NodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    return (int) $nodeStorage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->count()
      ->execute();
  }

}
