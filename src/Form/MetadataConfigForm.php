<?php

declare(strict_types=1);

namespace Drupal\static_metadata_records\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Static Metadata Records settings for this site.
 */
final class MetadataConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'static_metadata_records_metadata_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['static_metadata_records.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // extract all possible fields from drupal
    $content = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'islandora_object');
    $options = ['' => 'Please select a field'];
    foreach ($content as $name => $value) {
        // filter only the Plain Text (long) fields
        if ($value->getType() === "string_long"){
          $options[$name] = $value->getLabel() . " (" . $name . ")";
        }
    }

    $form['dc_field_selection'] = [
      '#type' => 'select',
      '#title' => $this->t('Dublin Core (DC) Destination Field'),
      '#description' => $this->t('Choose the field where the raw DC XML will be stored.'),
      '#default_value' => $this->config('static_metadata_records.settings')->get('dc_field_selection'),
      '#options' => $options,
    ];
      
    $form['mods_field_selection'] = [
      '#type' => 'select',
      '#title' => $this->t('MODS Destination Field'),
      '#description' => $this->t('Choose the field where the raw MODS XML will be stored.'),
      '#default_value' => $this->config('static_metadata_records.settings')->get('mods_field_selection'),
      '#options' => $options,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('static_metadata_records.settings')
      ->set('dc_field_selection', $form_state->getValue('dc_field_selection'))
      ->set('mods_field_selection', $form_state->getValue('mods_field_selection'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
