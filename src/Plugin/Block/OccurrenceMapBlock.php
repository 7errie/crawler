<?php

/**
 * @file
 * Contains \Drupal\crawler\Plugin\Block\OccurrenceMapBlock
 */

namespace Drupal\crawler\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Guzzle\Client;

/**
 * Provides blocks bundled for the Crawler module.
 *
 * @Block(
 *   id = "gbif_block",
 *   admin_label = @Translation("GBIF Occurrence"),
 *   category = @Translation("Crawler")
 * )
 */

class OccurrenceMapBlock extends BlockBase
{
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    /* Get page title */
    $request = \Drupal::request();
    if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
      $title = \Drupal::service('title_resolver')->getTitle($request, $route);
    }

    $surl = 'https://api.gbif.org/v1/species?name='.$title;
    $ourl = 'https://api.gbif.org/v1/occurrence/search?taxonKey=';

    $time = 1;
    $taxonkey = '';
    $name = '';

    /* Get configs */
    $config = \Drupal::config('crawler.settings');
    $h = $config->get('block_setting.gbif.size.height');
    $w = $config->get('block_setting.gbif.size.width');
    
    if (empty($h)) {
      $h = '250';
    }
    if (empty($w)) {
      $w = '400';
    }
    
    /* Set options for http request */
    $options = array(
      'timeout' => $time,
    );
    try {
      $response = \Drupal::httpClient()->get($surl);
      $data = json_decode((string) $response->getBody());

      if (empty($data) || $data->results[0] == '') {
        return;
      }
      foreach ($data as $d) {
        if (!empty($d[0]->key)) {
          $key = $d[0]->key;
          break;
        }
      }
      try {
        $response = \Drupal::httpClient()->get($ourl.$key);
        $data = json_decode((string) $response->getBody());
        
        if (empty($data) || $data->results[0] == '') {
          return;
        }
        foreach ($data as $d) {
          if (!empty($d[0]->taxonKey)) {
            $taxonkey = $d[0]->taxonKey;
            $name = $d[0]->species;
            break;
          }
        }
      } catch (RequestException $e) {
        return $e;
      }
    } catch (RequestException $e) {
      return $e;
    }

    $build = [];
    $build[] = array(
      '#type' => 'markup',
      '#markup' => \Drupal\Core\Render\Markup::create('<div id="gbif-map" style="height: '.$h.'px; width: '.$w.'px;"></div>'),
      '#attached' =>
      [
        'library' => [
          'crawler/occurrence_js',
        ],
      ],
    );
    $build['#attached']['drupalSettings']['crawler']['gbif'] = $taxonkey;
    $build['#attached']['drupalSettings']['crawler']['gbif_sp_name'] = $name;
    return $build;
  }
}
