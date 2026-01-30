<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Provides a form for event registration.
 */
class EventRegistrationForm extends FormBase {

  /**
   * The event registration service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationService
   */
  protected $eventRegistrationService;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new EventRegistrationForm.
   *
   * @param \Drupal\event_registration\Service\EventRegistrationService $event_registration_service
   *   The event registration service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EventRegistrationService $event_registration_service, MessengerInterface $messenger) {
    $this->eventRegistrationService = $event_registration_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.service'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if registration is currently open.
    $categories = $this->eventRegistrationService->getEventCategories();
    
    if (empty($categories)) {
      $form['message'] = [
        '#markup' => '<p>' . $this->t('No events are currently open for registration.') . '</p>',
      ];
      return $form;
    }

    $form['#prefix'] = '<div id="event-registration-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#required' => TRUE,
      '#options' => ['' => $this->t('- Select Category -')] + $categories,
      '#ajax' => [
        'callback' => '::updateEventDateOptions',
        'wrapper' => 'event-date-wrapper',
        'event' => 'change',
      ],
    ];

    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-date-wrapper'],
    ];

    $selected_category = $form_state->getValue('event_category');
    $event_dates = [];
    
    if (!empty($selected_category)) {
      $event_dates = $this->eventRegistrationService->getEventDatesByCategory($selected_category);
    }

    $form['event_date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
      '#options' => ['' => $this->t('- Select Date -')] + $event_dates,
      '#ajax' => [
        'callback' => '::updateEventNameOptions',
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $selected_date = $form_state->getValue('event_date');
    $event_names = [];
    
    if (!empty($selected_category) && !empty($selected_date)) {
      $event_names = $this->eventRegistrationService->getEventNamesByCategoryAndDate($selected_category, $selected_date);
    }

    $form['event_name_wrapper']['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#options' => ['' => $this->t('- Select Event -')] + $event_names,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register for Event'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * AJAX callback to update event date options.
   */
  public function updateEventDateOptions(array &$form, FormStateInterface $form_state) {
    return $form['event_date_wrapper'];
  }

  /**
   * AJAX callback to update event name options.
   */
  public function updateEventNameOptions(array &$form, FormStateInterface $form_state) {
    return $form['event_name_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $full_name = $form_state->getValue('full_name');
    $email = $form_state->getValue('email');
    $college_name = $form_state->getValue('college_name');
    $department = $form_state->getValue('department');
    $event_date = $form_state->getValue('event_date');

    // Validate special characters in text fields.
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $full_name)) {
      $form_state->setErrorByName('full_name', $this->t('Full name should not contain special characters.'));
    }

    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $college_name)) {
      $form_state->setErrorByName('college_name', $this->t('College name should not contain special characters.'));
    }

    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $department)) {
      $form_state->setErrorByName('department', $this->t('Department should not contain special characters.'));
    }

    // Validate email format (already handled by #type => 'email', but we can add custom validation).
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }

    // Check for duplicate registration.
    if (!empty($email) && !empty($event_date)) {
      if ($this->eventRegistrationService->checkDuplicateRegistration($email, $event_date)) {
        $form_state->setErrorByName('email', $this->t('You have already registered for this event on @date.', ['@date' => $event_date]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event_config_id = $form_state->getValue('event_name');
    $event_name = $this->eventRegistrationService->getEventNameById($event_config_id);

    $data = [
      'full_name' => $form_state->getValue('full_name'),
      'email' => $form_state->getValue('email'),
      'college_name' => $form_state->getValue('college_name'),
      'department' => $form_state->getValue('department'),
      'event_category' => $form_state->getValue('event_category'),
      'event_date' => $form_state->getValue('event_date'),
      'event_config_id' => $event_config_id,
      'event_name' => $event_name,
    ];

    $registration_id = $this->eventRegistrationService->saveRegistration($data);

    if ($registration_id) {
      // Send confirmation emails.
      $this->eventRegistrationService->sendConfirmationEmails($data);
      
      $this->messenger->addMessage($this->t('Thank you for registering! A confirmation email has been sent to @email.', [
        '@email' => $data['email'],
      ]));

      // Reset the form.
      $form_state->setRebuild();
    }
    else {
      $this->messenger->addError($this->t('An error occurred while processing your registration. Please try again.'));
    }
  }

}
