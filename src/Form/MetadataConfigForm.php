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
		$config = $this->config('static_metadata_records.settings');

		// hooks
		$form['hooks_heading'] = [
			'#type' => 'markup',
			'#markup' => '<h3>Custom Module Hooks</h3>',
		];
		$form['enable_hooks'] = [
			'#type' => 'checkbox',
			'#title' => $this->t('Enable Hooks (create node AND update node)'),
			'#default_value' => $config->get('enable_hooks'),
		];

		// extract all possibl	e fields from drupal
		$content = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'islandora_object');
		$plainTextOptions = ['' => 'Please select a field'];

		foreach ($content as $name => $value) {
			// filter only the Plain Text (long) fields
			if ($value->getType() === "string_long"){
				$plainTextOptions[$name] = $value->getLabel() . " (" . $name . ")";
			}
		}

		// dc and mods destination fields
		$form['destination_heading'] = [
			'#type' => 'markup',
			'#markup' => '<br><hr><br><h3>Destination Fields</h3>',
		];
		$form['dc_field_selection'] = [
			'#type' => 'select',
			'#title' => $this->t('Dublin Core (DC) Destination Field'),
			'#description' => $this->t('Choose the field where the raw DC XML will be stored.'),
			'#default_value' => $this->config('static_metadata_records.settings')->get('dc_field_selection'),
			'#options' => $plainTextOptions,
		];
		$form['mods_field_selection'] = [
			'#type' => 'select',
			'#title' => $this->t('MODS Destination Field'),
			'#description' => $this->t('Choose the field where the raw MODS XML will be stored.'),
			'#default_value' => $this->config('static_metadata_records.settings')->get('mods_field_selection'),
			'#options' => $plainTextOptions,
		];

		// exclusion
		$form['exclusion_heading'] = [
			'#type' => 'markup',
			'#markup' => '<br><hr><br><h3>Content Type Specific Exclusion</h3>',
		];
		$node_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
		$options = [];
		foreach ($node_types as $node_type) {
			$options[$node_type->id()] = $node_type->label();
		}
		
		$form['excluded_content_types'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Content Type to Filter'),
            '#options' => $options,
            '#default_value' => $config->get('excluded_content_types') ?: [],
        ];

        $form['excluded_field_machine_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Field Machine Name'),
            '#description' => $this->t('e.g., field_model'),
            '#default_value' => $config->get('excluded_field_machine_name'),
        ];

        $form['excluded_field_value'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Field Value'),
            '#description' => $this->t('XML Records will NOT be generated if this value is found in the field above. (e.g. Collection)'),
            '#default_value' => $config->get('excluded_field_value'),
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
			->set('enable_hooks', $form_state->getValue('enable_hooks'))
			->set('excluded_content_types', $form_state->getValue('excluded_content_types'))
            ->set('excluded_field_machine_name', $form_state->getValue('excluded_field_machine_name'))
            ->set('excluded_field_value', $form_state->getValue('excluded_field_value'))
			->save();

		parent::submitForm($form, $form_state);
	}
}