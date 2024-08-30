<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure the Event Registration module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_registration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('event_registration.settings')
      ->get('departments');

    $tableHeader = [
      'name' => $this->t('Name'),
      'machine_name' => $this->t('Machine Name'),
    ];

    foreach ($config as $machine_name => $name) {
      $rows[] = [
        'name' => $name,
        'machine_name' => $machine_name,
      ];
    }

    $form['existing_departments'] = [
      '#type' => 'details',
      '#title' => $this->t('Existing departments'),
      '#open' => FALSE,
    ];

    $form['existing_departments']['table'] = [
      '#type' => 'table',
      '#header' => $tableHeader,
      '#rows' => $rows,
      '#empty' => $this->t('No departments found'),
    ];

    $form['add_new'] = [
      '#type' => 'details',
      '#title' => $this->t('Add new department'),
      '#open' => TRUE,
    ];

    $form['add_new']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department Name'),
      '#default_value' => $this->config('event_registration.settings')->get('name'),
      '#required' => TRUE,
      '#max_length' => 255,
    ];
    $form['add_new']['machine_name'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine Name'),
      '#default_value' => $this->config('event_registration.settings')->get('name'),
      '#machine_name' => [
        'exists' => [$this, 'machineNameExists'],
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => ['new_storage_wrapper', 'label'],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state
      ->cleanValues()
      ->getValues();

    // Get the existing departments in config.
    $config = $this->config('event_registration.settings')
      ->get('departments');

    $config[$values['machine_name']] = $values['name'];

    // Save the departmetns.
    $this->config('event_registration.settings')
      ->set('departments', $config)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Checks if a field machine name is taken.
   *
   * @param string $value
   *   The machine name, not prefixed.
   * @param array $element
   *   An array containing the structure of the 'field_name' element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   Whether or not the field machine name is taken.
   */
  public function machineNameExists($value, array $element, FormStateInterface $form_state) {

    $config = $this->config('event_registration.settings')
      ->get('departments');

    if (array_key_exists($value, $config)) {
      return TRUE;
    }
    return FALSE;
  }

}
