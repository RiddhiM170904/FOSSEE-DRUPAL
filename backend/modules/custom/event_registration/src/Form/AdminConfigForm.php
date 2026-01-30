<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Event Registration settings.
 */
class AdminConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_admin_config';
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
    $config = $this->config('event_registration.settings');

    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin Notification Email Address'),
      '#description' => $this->t('Email address to receive registration notifications.'),
      '#default_value' => $config->get('admin_email'),
      '#required' => TRUE,
    ];

    $form['enable_admin_notifications'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Admin Notifications'),
      '#description' => $this->t('Check this box to send email notifications to the admin for each registration.'),
      '#default_value' => $config->get('enable_admin_notifications') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('admin_email');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('admin_email', $this->t('Please enter a valid email address.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('event_registration.settings')
      ->set('admin_email', $form_state->getValue('admin_email'))
      ->set('enable_admin_notifications', $form_state->getValue('enable_admin_notifications'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
