<?php
/**
 * @file
 * Contains \Drupal\crawler\Plugin\Block\RedListBlock
 */

namespace Drupal\crawler\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/* Check which category from the data obtained */
function catList($cat) {
  switch ($cat) {
    case 'EX':
      return 'Extinct (EX)';
      break;
    case 'CR':
      return 'Critically Endangered (CR)';
      break;
    case 'EN':
      return 'Endangered (EN)';
      break;
    case 'VU':
      return 'Vulnerable (VU)';
      break;
    case 'CR':
      return 'Near Threatened (NT)';
      break;
    case 'LC':
      return 'Least Concern (LC)';
      break;
    case 'DD':
      return 'Data Deficient (DD)';
      break;
    case 'NE':
      return 'Not Evaluated (NE)';
      break;
  }
}

/**
 * Provides blocks bundled for the Crawler module.
 *
 * @Block(
 *   id = "iucn_block",
 *   admin_label = @Translation("IUCN Red List"),
 *   category = @Translation("Crawler")
 * )
 */

class RedListBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    /* Get page title */
    $request = \Drupal::request();
    if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
      $title = \Drupal::service('title_resolver')->getTitle($request, $route);
    }
    
	  $time = 1;

	  /** Set options for http request */
    $options = array(
      'timeout' => $time,
    );

    /** Get configs */
    $config = \Drupal::config('crawler.settings');
    $key = $config->get('block_setting.iucn.api_key');

    $url = 'http://apiv3.iucnredlist.org/api/v3/species/'.$title.'?token='.$key;
    $url2 = 'http://apiv3.iucnredlist.org/api/v3/species/narrative/'.$title.'?token='.$key;

    $content = '';
    try {
      /** Block access if iucn api key is not set in the module setting */
      if (empty($key)) {
        $content .= 'Invalid token.';
        \Drupal::messenger()->addMessage('API token required. Please get the key and save it in the crawler configuration page.', MessengerInterface::TYPE_ERROR);
        return;
      }
      /** Open connection with iucn server and fetch species common name and iucn status */
      $response = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'application/xml')));
      $data = json_decode((string) $response->getBody());

      if (empty($data) || $data->result[0] == '') {
        return;
      }
      
      foreach ($data as $d) {
        $content = 'Common name: ' . $d[0]->main_common_name . '<br /><strong>' . catList($d[0]->category) . '</strong><br />';
      }

      try {
        /** Open another connection with iucn server and fetch threats text */
        $response = \Drupal::httpClient()->get($url2, array('headers' => array('Accept' => 'application/json')));
        $data = json_decode((string) $response->getBody());
        if (empty($data) || $data->result[0] == '') {
          return;
        }
        
        foreach ($data as $d) {
          $content .= $d[0]->threats;
        }
      }
      catch (RequestException $e) {
        return $e;
      }
    }
    catch (RequestException $e) {
      return $e;
    }
    
    /** Embed the html script with the data into the red list block */
    $build = [];
    $build[] = array(
      '#type' => 'markup',
      '#markup' => $content,
    );
    return $build;
  }
}