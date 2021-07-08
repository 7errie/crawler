<?php

/**
 * @file
 * Contains \Drupal\crawler\Plugin\Block\PubsBlock
 */

namespace Drupal\crawler\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger;

/**
 * Provides blocks bundled for the Crawler module.
 *
 * @Block(
 *   id = "pubs_block",
 *   admin_label = @Translation("Publications"),
 *   category = @Translation("Crawler")
 * )
 */

class PubsBlock extends BlockBase
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

    $time = 1;

    /* Set options for http request */
    $options = array(
      'timeout' => $time,
    );

    /* Get configs */
    $config = \Drupal::config('crawler.settings');
    $key = $config->get('block_setting.publication.api_key');

    $url = 'https://api.elsevier.com/content/search/scopus?httpAccept=application/json&apiKey='.$key.'&count=5&subj=EART,AGRI&query=' . $title;

    $list = '';
    try {
      if (empty($key)) {
        $list .= 'Invalid API key.';
        \Drupal::messenger()->addMessage('API key required. Please get the key and save it in the crawler configuration page.', MessengerInterface::TYPE_ERROR);
        return;
      }
      $response = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'application/json')));
      $data = json_decode((string) $response->getBody(), true);

      if (empty($data) || $data->totalResults == '0') {
        return;
      }
      
      foreach ($data as $key => $value) {
        for ($i=0;$i<count($value["entry"]);$i++) { /* Loop through the publications found */
          if (!empty($value["entry"][$i]["pii"])) {
            $articleUrl = '<h5><a href="https://www.sciencedirect.com/science/article/abs/pii/' . $value["entry"][$i]["pii"] . '">' . $value["entry"][$i]["dc:title"] . ' (' . $value["entry"][$i]["dc:creator"] . ', ' . substr($value["entry"][$i]["prism:coverDate"],0,4) . ')</a></h5>';
          } else {
            $articleUrl = '<h5>' . $value["entry"][$i]["dc:title"] . ' (' . $value["entry"][$i]["dc:creator"] . ', ' . substr($value["entry"][$i]["prism:coverDate"],0,4) . ')</h5>';
          }
          $pub = $articleUrl . 'Doi ' . $value["entry"][$i]["prism:doi"] . '<br>';
          $list.=$pub;
        }
      }
      $list.= '<br /><br /><a href="https://www.sciencedirect.com/search?qs=' . $title . '">More..</a>';
      //echo '<script>console.log('.json_encode($list).')</script>';
    } catch (RequestException $e) {
      return $e;
    }
    $build = [];
    $build[] = array(
      '#type' => 'markup',
      '#markup' => $list,
    );
    return $build;
  }
}
