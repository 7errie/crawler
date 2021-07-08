<?php

/**
 * @file
 * Contains \Drupal\crawler\Form\CrawlerSettingsForm
 */

namespace Drupal\crawler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CrawlerSettingsForm extends ConfigFormBase {
  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'crawler.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crawler_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Default settings.
    $config = $this->config(static::SETTINGS);

    $form['gbif_map_distribution'] = array(
      '#type' => 'fieldset',
      '#title' => t('GBIF Occurrence Map Options'),
      );
    $form['gbif_map_distribution']['gbif_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width:'),
      '#default_value' => $config->get('block_setting.gbif.size.width'),
      '#description' => $this->t('Width of gbif map in pixels.'),
    ];
    $form['gbif_map_distribution']['gbif_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height:'),
      '#default_value' => $config->get('block_setting.gbif.size.height'),
      '#description' => $this->t('Height of gbif map in pixels.'),
    ];
    $form['publication'] = array(
      '#type' => 'fieldset',
      '#title' => t('Publications Options'),
      );
    $form['publication']['pubs_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key:'),
      '#required' => TRUE,
      '#default_value' => $config->get('block_setting.publication.api_key'),
      '#description' => $this->t('API key acquired from <a href=":pubs">Scopus</a>. This field is mandatory.', array(':pubs' => 'https://dev.elsevier.com/')),
    ];
    $form['red_list'] = array(
      '#type' => 'fieldset',
      '#title' => t('Red List Options'),
      );
    $form['red_list']['iucn_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token:'),
      '#required' => TRUE,
      '#default_value' => $config->get('block_setting.iucn.api_key'),
      '#description' => $this->t('Token acquired from <a href=":iucn">IUCN</a>. This field is mandatory.', array(':iucn' => 'http://apiv3.iucnredlist.org/api/v3/token')),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    print_r($form_state->getValue('gbif_width'));
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('block_setting.gbif.size.width', $form_state->getValue('gbif_width'))
      ->set('block_setting.gbif.size.height', $form_state->getValue('gbif_height'))
      ->set('block_setting.publication.api_key', $form_state->getValue('pubs_key'))
      ->set('block_setting.iucn.api_key', $form_state->getValue('iucn_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}