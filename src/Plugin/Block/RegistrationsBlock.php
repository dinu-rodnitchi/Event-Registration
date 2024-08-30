<?php

namespace Drupal\event_registration\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays the number of registrations.
 *
 * @Block(
 *   id = "event_registration_block",
 *   admin_label = @Translation("Event Registration"),
 *   category = @Translation("Event Registration")
 * )
 */
class RegistrationsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The registration service.
   *
   * @var \Drupal\event_registration\EventRegistrationService
   */
  protected $registrationService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->registrationService = $container->get('event_registration.service');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $count = $this->registrationService->registrationsCount();

    // Build the block content.
    $build['content'] = [
      '#markup' => $this->t('Registrations count: @count', ['@count' => $count]),
      // Add the cache tags to invalidate the block when the node list changes.
      '#cache' => [
        'tags' => ['node_list:registration'],
      ],
    ];

    // Add a link to the block.
    $build['add_registration'] = [
      '#type' => 'link',
      '#prefix' => '<hr/>',
      '#title' => $this->t('Register'),
      '#url' => Url::fromRoute('event_registration.registration'),
    ];

    return $build;
  }

}
