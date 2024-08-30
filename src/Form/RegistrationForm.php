<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Event Registration form.
 *
 * The form is used to register for an event.
 */
class RegistrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_registration';
  }

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Form constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $department = NULL) {

    $config = $this->configFactory()
      ->get('event_registration.settings');

    $departments = $config->get('departments');

    if (!$department || !array_key_exists($department, $departments)) {
      $form['department'] = [
        '#type' => 'select',
        '#title' => $this->t('Select the department'),
        '#required' => TRUE,
        '#options' => $departments,
        '#default_value' => $department,
      ];

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'department_select',
        '#value' => $this->t('Select'),
      ];

      return $form;
    }

    // Set the department in form state.
    $form_state->set('department', $departments[$department]);

    $form['employee_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the employee'),
      '#required' => TRUE,
    ];

    $form['one_plus'] = [
      '#type' => 'radios',
      '#title' => $this->t('One plus'),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#required' => TRUE,
    ];

    $form['amount_of_kids'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of kids'),
      '#required' => TRUE,
      '#min' => 0,
    ];

    $form['amount_of_vegetarians'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of vegetarians'),
      '#required' => TRUE,
      '#min' => 0,
    ];

    $form['email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'register',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $triggeringElement = $form_state->getTriggeringElement();

    if ($triggeringElement['#name'] === 'department_select') {
      return;
    }

    $values = $form_state->cleanValues()->getValues();

    $peopleAmount = 1 + $values['amount_of_kids'] + (int) ($values['one_plus'] === 'yes');

    if ($values['amount_of_vegetarians'] > $peopleAmount) {
      $form_state->setErrorByName('amount_of_vegetarians', $this->t('The amount of vegetarians can not exceed the participants count (@count)', ['@count' => $peopleAmount]));
    }

    /** @var \Drupal\node\NodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $nodes = $nodeStorage->loadByProperties([
      'status' => 1,
      'field_email_address' => $values['email_address'],
    ]);

    if (!empty($nodes)) {
      $form_state->setErrorByName('email_address', $this->t('This email is already registered.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $triggeringElement = $form_state->getTriggeringElement();

    if ($triggeringElement['#name'] === 'department_select') {
      $form_state->setRedirect('event_registration.registration', ['department' => $form_state->cleanValues()->getValue('department')]);
    }
    else {

      $values = $form_state->cleanValues()->getValues();

      $data = [
        'type' => 'registration',
        'title' => $values['employee_name'],
        'field_department' => $form_state->get('department'),

        'field_one_plus' => $values['one_plus'] === 'yes',
        'field_amount_of_kids' => $values['amount_of_kids'],
        'field_amount_of_vegetarians' => $values['amount_of_vegetarians'],
        'field_email_address' => $values['email_address'],
      ];

      /** @var \Drupal\node\NodeStorage */
      $nodeStorage = $this->entityTypeManager->getStorage('node');

      try {
        $node = $nodeStorage
          ->create($data);
        $node->save();
        $this->messenger()->addMessage($this->t('Registration complete.'));
      }
      catch (\Exception $e) {
        $this->messenger()->addError($this->t('Something went wrong.'));
      }

    }
  }

}
