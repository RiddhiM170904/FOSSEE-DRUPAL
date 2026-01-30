<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Provides a form for configuring events.
 */
class EventConfigForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new EventConfigForm.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Connection $database, MessengerInterface $messenger) {
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#required' => TRUE,
      '#options' => [
        '' => $this->t('- Select Category -'),
        'Online Workshop' => $this->t('Online Workshop'),
        'Hackathon' => $this->t('Hackathon'),
        'Conference' => $this->t('Conference'),
        'One-day Workshop' => $this->t('One-day Workshop'),
      ],
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['registration_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Registration Start Date'),
      '#required' => TRUE,
    ];

    $form['registration_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Registration End Date'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event Configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $event_name = $form_state->getValue('event_name');
    $registration_start = $form_state->getValue('registration_start_date');
    $registration_end = $form_state->getValue('registration_end_date');
    $event_date = $form_state->getValue('event_date');

    // Validate event name for special characters.
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $event_name)) {
      $form_state->setErrorByName('event_name', $this->t('Event name should not contain special characters.'));
    }

    // Validate dates.
    if ($registration_start >= $registration_end) {
      $form_state->setErrorByName('registration_end_date', $this->t('Registration end date must be after the start date.'));
    }

    if ($event_date < $registration_start) {
      $form_state->setErrorByName('event_date', $this->t('Event date cannot be before the registration start date.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->database->insert('event_config')
        ->fields([
          'event_name' => $form_state->getValue('event_name'),
          'event_category' => $form_state->getValue('event_category'),
          'event_date' => $form_state->getValue('event_date'),
          'registration_start_date' => $form_state->getValue('registration_start_date'),
          'registration_end_date' => $form_state->getValue('registration_end_date'),
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();

      $this->messenger->addMessage($this->t('Event configuration has been saved successfully.'));
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('An error occurred while saving the event configuration.'));
      \Drupal::logger('event_registration')->error($e->getMessage());
    }
  }

}
