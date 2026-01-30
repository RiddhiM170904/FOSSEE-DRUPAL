<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_registration\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Controller for admin listing of event registrations.
 */
class AdminListingController extends ControllerBase {

  /**
   * The event registration service.
   *
   * @var \Drupal\event_registration\Service\EventRegistrationService
   */
  protected $eventRegistrationService;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new AdminListingController.
   *
   * @param \Drupal\event_registration\Service\EventRegistrationService $event_registration_service
   *   The event registration service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(EventRegistrationService $event_registration_service, RequestStack $request_stack) {
    $this->eventRegistrationService = $event_registration_service;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.service'),
      $container->get('request_stack')
    );
  }

  /**
   * Display the admin listing page.
   */
  public function listing() {
    $build = [];

    $build['#attached']['library'][] = 'core/drupal.ajax';

    // Event date dropdown.
    $event_dates = $this->eventRegistrationService->getAllEventDates();
    
    $build['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['event-filters']],
    ];

    $build['filters']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => ['' => $this->t('- Select Date -')] + $event_dates,
      '#attributes' => [
        'id' => 'event-date-select',
        'class' => ['use-ajax'],
      ],
      '#ajax' => [
        'callback' => [$this, 'updateEventNames'],
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    $build['filters']['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $build['filters']['event_name_wrapper']['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => ['' => $this->t('- Select Event -')],
      '#attributes' => [
        'id' => 'event-name-select',
        'class' => ['use-ajax'],
      ],
      '#ajax' => [
        'callback' => [$this, 'updateRegistrations'],
        'wrapper' => 'registrations-wrapper',
        'event' => 'change',
      ],
    ];

    $build['export_link'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'export-link-wrapper'],
    ];

    $build['registrations_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'registrations-wrapper'],
    ];

    $build['registrations_wrapper']['info'] = [
      '#markup' => '<p>' . $this->t('Please select an event date and event name to view registrations.') . '</p>',
    ];

    return $build;
  }

  /**
   * AJAX callback to update event names.
   */
  public function updateEventNames(array &$form, $form_state = NULL) {
    $request = $this->requestStack->getCurrentRequest();
    $event_date = $request->query->get('event_date');

    if (empty($event_date)) {
      $event_date = $request->request->get('event_date');
    }

    $event_names = [];
    if (!empty($event_date)) {
      $event_names = $this->eventRegistrationService->getEventNamesByDate($event_date);
    }

    $response = new AjaxResponse();
    
    $content = '<div id="event-name-wrapper">';
    $content .= '<label for="event-name-select">' . $this->t('Event Name') . '</label>';
    $content .= '<select id="event-name-select" class="use-ajax form-select" name="event_name">';
    $content .= '<option value="">' . $this->t('- Select Event -') . '</option>';
    
    foreach ($event_names as $id => $name) {
      $content .= '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
    }
    
    $content .= '</select></div>';

    $response->addCommand(new ReplaceCommand('#event-name-wrapper', $content));
    
    // Reset registrations display.
    $reset_content = '<div id="registrations-wrapper"><p>' . $this->t('Please select an event name.') . '</p></div>';
    $response->addCommand(new ReplaceCommand('#registrations-wrapper', $reset_content));

    return $response;
  }

  /**
   * AJAX callback to update registrations.
   */
  public function updateRegistrations(array &$form = NULL, $form_state = NULL) {
    $request = $this->requestStack->getCurrentRequest();
    $event_date = $request->query->get('event_date');
    $event_config_id = $request->query->get('event_name');

    if (empty($event_date)) {
      $event_date = $request->request->get('event_date');
    }
    if (empty($event_config_id)) {
      $event_config_id = $request->request->get('event_name');
    }

    $response = new AjaxResponse();

    if (empty($event_date) || empty($event_config_id)) {
      $content = '<div id="registrations-wrapper"><p>' . $this->t('Please select both event date and event name.') . '</p></div>';
      $response->addCommand(new ReplaceCommand('#registrations-wrapper', $content));
      return $response;
    }

    $registrations = $this->eventRegistrationService->getRegistrationsByEvent($event_date, $event_config_id);
    $total_participants = count($registrations);

    $content = '<div id="registrations-wrapper">';
    $content .= '<h3>' . $this->t('Total Participants: @count', ['@count' => $total_participants]) . '</h3>';

    if (!empty($registrations)) {
      $content .= '<table class="table">';
      $content .= '<thead><tr>';
      $content .= '<th>' . $this->t('Name') . '</th>';
      $content .= '<th>' . $this->t('Email') . '</th>';
      $content .= '<th>' . $this->t('Event Date') . '</th>';
      $content .= '<th>' . $this->t('College Name') . '</th>';
      $content .= '<th>' . $this->t('Department') . '</th>';
      $content .= '<th>' . $this->t('Submission Date') . '</th>';
      $content .= '</tr></thead><tbody>';

      foreach ($registrations as $registration) {
        $content .= '<tr>';
        $content .= '<td>' . htmlspecialchars($registration->full_name) . '</td>';
        $content .= '<td>' . htmlspecialchars($registration->email) . '</td>';
        $content .= '<td>' . htmlspecialchars($registration->event_date) . '</td>';
        $content .= '<td>' . htmlspecialchars($registration->college_name) . '</td>';
        $content .= '<td>' . htmlspecialchars($registration->department) . '</td>';
        $content .= '<td>' . date('Y-m-d H:i:s', $registration->created) . '</td>';
        $content .= '</tr>';
      }

      $content .= '</tbody></table>';
    }
    else {
      $content .= '<p>' . $this->t('No registrations found for this event.') . '</p>';
    }

    $content .= '</div>';

    $response->addCommand(new ReplaceCommand('#registrations-wrapper', $content));

    // Update export link.
    $export_url = '/admin/event-registration/export?event_date=' . urlencode($event_date) . '&event_config_id=' . urlencode($event_config_id);
    $export_content = '<div id="export-link-wrapper">';
    $export_content .= '<a href="' . $export_url . '" class="button button--primary">' . $this->t('Export to CSV') . '</a>';
    $export_content .= '</div>';
    $response->addCommand(new ReplaceCommand('#export-link-wrapper', $export_content));

    return $response;
  }

  /**
   * Export registrations to CSV.
   */
  public function exportCsv() {
    $request = $this->requestStack->getCurrentRequest();
    $event_date = $request->query->get('event_date');
    $event_config_id = $request->query->get('event_config_id');

    if (empty($event_date)) {
      $this->messenger()->addError($this->t('Event date is required.'));
      return $this->redirect('event_registration.admin_listing');
    }

    $registrations = $this->eventRegistrationService->getRegistrationsByEvent($event_date, $event_config_id);

    // Create CSV content.
    $csv_data = [];
    $csv_data[] = [
      'Name',
      'Email',
      'College Name',
      'Department',
      'Event Category',
      'Event Date',
      'Submission Date',
    ];

    foreach ($registrations as $registration) {
      $csv_data[] = [
        $registration->full_name,
        $registration->email,
        $registration->college_name,
        $registration->department,
        $registration->event_category,
        $registration->event_date,
        date('Y-m-d H:i:s', $registration->created),
      ];
    }

    // Generate CSV.
    $output = fopen('php://temp', 'r+');
    foreach ($csv_data as $row) {
      fputcsv($output, $row);
    }
    rewind($output);
    $csv_content = stream_get_contents($output);
    fclose($output);

    // Create response.
    $response = new Response($csv_content);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="event_registrations_' . $event_date . '.csv"');

    return $response;
  }

}
