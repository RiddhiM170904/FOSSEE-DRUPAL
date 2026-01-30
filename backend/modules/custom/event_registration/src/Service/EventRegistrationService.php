<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service for handling event registrations.
 */
class EventRegistrationService {

  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new EventRegistrationService.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(Connection $database, MailManagerInterface $mail_manager, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Get available event categories.
   *
   * @return array
   *   Array of event categories.
   */
  public function getEventCategories() {
    $current_date = date('Y-m-d');
    
    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_category'])
      ->condition('registration_start_date', $current_date, '<=')
      ->condition('registration_end_date', $current_date, '>=')
      ->distinct();
    
    $results = $query->execute()->fetchCol();
    
    $categories = [];
    foreach ($results as $category) {
      $categories[$category] = $category;
    }
    
    return $categories;
  }

  /**
   * Get event dates by category.
   *
   * @param string $category
   *   The event category.
   *
   * @return array
   *   Array of event dates.
   */
  public function getEventDatesByCategory($category) {
    if (empty($category)) {
      return [];
    }

    $current_date = date('Y-m-d');
    
    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_date'])
      ->condition('event_category', $category)
      ->condition('registration_start_date', $current_date, '<=')
      ->condition('registration_end_date', $current_date, '>=')
      ->distinct();
    
    $results = $query->execute()->fetchCol();
    
    $dates = [];
    foreach ($results as $date) {
      $dates[$date] = $date;
    }
    
    return $dates;
  }

  /**
   * Get event names by category and date.
   *
   * @param string $category
   *   The event category.
   * @param string $date
   *   The event date.
   *
   * @return array
   *   Array of event names with IDs as keys.
   */
  public function getEventNamesByCategoryAndDate($category, $date) {
    if (empty($category) || empty($date)) {
      return [];
    }

    $current_date = date('Y-m-d');
    
    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_category', $category)
      ->condition('event_date', $date)
      ->condition('registration_start_date', $current_date, '<=')
      ->condition('registration_end_date', $current_date, '>=');
    
    $results = $query->execute()->fetchAllKeyed();
    
    return $results;
  }

  /**
   * Check if registration already exists.
   *
   * @param string $email
   *   The email address.
   * @param string $event_date
   *   The event date.
   *
   * @return bool
   *   TRUE if registration exists, FALSE otherwise.
   */
  public function checkDuplicateRegistration($email, $event_date) {
    $query = $this->database->select('event_registration', 'er')
      ->condition('email', $email)
      ->condition('event_date', $event_date);
    
    $count = $query->countQuery()->execute()->fetchField();
    
    return $count > 0;
  }

  /**
   * Save event registration.
   *
   * @param array $data
   *   Registration data.
   *
   * @return int|bool
   *   The registration ID on success, FALSE on failure.
   */
  public function saveRegistration(array $data) {
    try {
      $id = $this->database->insert('event_registration')
        ->fields([
          'full_name' => $data['full_name'],
          'email' => $data['email'],
          'college_name' => $data['college_name'],
          'department' => $data['department'],
          'event_category' => $data['event_category'],
          'event_date' => $data['event_date'],
          'event_config_id' => $data['event_config_id'],
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();

      return $id;
    }
    catch (\Exception $e) {
      \Drupal::logger('event_registration')->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Send confirmation emails.
   *
   * @param array $data
   *   Registration data including event_name.
   */
  public function sendConfirmationEmails(array $data) {
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    
    // Send email to user.
    $this->sendEmail($data['email'], $data, $langcode);
    
    // Send email to admin if enabled.
    $config = $this->configFactory->get('event_registration.settings');
    $admin_notifications_enabled = $config->get('enable_admin_notifications') ?? FALSE;
    $admin_email = $config->get('admin_email');
    
    if ($admin_notifications_enabled && !empty($admin_email)) {
      $this->sendEmail($admin_email, $data, $langcode);
    }
  }

  /**
   * Send email to a recipient.
   *
   * @param string $to
   *   The email address.
   * @param array $data
   *   Registration data.
   * @param string $langcode
   *   Language code.
   */
  protected function sendEmail($to, array $data, $langcode) {
    $params = [
      'full_name' => $data['full_name'],
      'event_name' => $data['event_name'],
      'event_date' => $data['event_date'],
      'event_category' => $data['event_category'],
    ];

    $this->mailManager->mail(
      'event_registration',
      'registration_confirmation',
      $to,
      $langcode,
      $params,
      NULL,
      TRUE
    );
  }

  /**
   * Get all event dates for dropdown (admin listing).
   *
   * @return array
   *   Array of event dates.
   */
  public function getAllEventDates() {
    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_date'])
      ->distinct()
      ->orderBy('event_date', 'DESC');
    
    $results = $query->execute()->fetchCol();
    
    $dates = [];
    foreach ($results as $date) {
      $dates[$date] = $date;
    }
    
    return $dates;
  }

  /**
   * Get event names by date (admin listing).
   *
   * @param string $date
   *   The event date.
   *
   * @return array
   *   Array of event names with IDs as keys.
   */
  public function getEventNamesByDate($date) {
    if (empty($date)) {
      return [];
    }

    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_date', $date);
    
    $results = $query->execute()->fetchAllKeyed();
    
    return $results;
  }

  /**
   * Get registrations by event.
   *
   * @param string $event_date
   *   The event date.
   * @param int $event_config_id
   *   The event config ID.
   *
   * @return array
   *   Array of registrations.
   */
  public function getRegistrationsByEvent($event_date, $event_config_id = NULL) {
    $query = $this->database->select('event_registration', 'er')
      ->fields('er', [
        'id',
        'full_name',
        'email',
        'college_name',
        'department',
        'event_category',
        'event_date',
        'created',
      ])
      ->condition('event_date', $event_date);

    if ($event_config_id !== NULL) {
      $query->condition('event_config_id', $event_config_id);
    }

    $query->orderBy('created', 'DESC');
    
    return $query->execute()->fetchAll();
  }

  /**
   * Get event name by config ID.
   *
   * @param int $event_config_id
   *   The event config ID.
   *
   * @return string|null
   *   The event name or NULL.
   */
  public function getEventNameById($event_config_id) {
    $query = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_name'])
      ->condition('id', $event_config_id);
    
    return $query->execute()->fetchField();
  }

}
