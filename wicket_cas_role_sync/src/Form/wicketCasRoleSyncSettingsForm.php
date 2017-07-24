<?php
// https://www.drupal.org/docs/8/api/configuration-api/working-with-configuration-forms
namespace Drupal\wicket_cas_role_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure wicket CAS role sync settings for this site.
 */
class wicketCasRoleSyncSettingsForm extends ConfigFormBase {

  const FORM_ID = 'wicket_cas_role_sync_admin_settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::FORM_ID;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wicket_cas_role_sync.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wicket_cas_role_sync.settings');

    // get all roles from Wicket to build the list
    $client = wicket_api_client();
    $roles = $client->roles->all();
    $role_options = [];
    if ($roles) {
      foreach ($roles as $role) {
        $role_options[$role->uuid] = $role->name;
      }
    }

    $form[$this->getFormId() . '_sync_all_roles'] = array(
      '#type' => 'checkbox',
      '#title' => t('Sync all roles'),
      '#default_value' => $config->get($this->getFormId() . '_sync_all_roles', ''),
      '#description' => t('If checked, all roles on each user logging in will be considered for synchronization into Drupal.'),
    );
    $form[$this->getFormId() . '_whitelisted_roles'] = array(
      '#type' => 'checkboxes',
      '#options' => $role_options,
      '#title' => t('Whitelisted Roles'),
      '#default_value' => $config->get($this->getFormId() . '_whitelisted_roles', ''),
      '#description' => t('These are all the roles in Wicket. Choose which to whitelist (which ones will be considered for synchronization when users log in to Drupal)'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    $this->config('wicket_cas_role_sync.settings')
      // Set the submitted configuration setting
      ->set($this->getFormId() . '_sync_all_roles', $form_state->getValue($this->getFormId() . '_sync_all_roles'))
      ->set($this->getFormId() . '_whitelisted_roles', $form_state->getValue($this->getFormId() . '_whitelisted_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}