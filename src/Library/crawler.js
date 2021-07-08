/**
 * @file
 * Contains \Drupal\crawler\Library\js
 */
(function ($, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.crawlerBehavior = {
    attach: function (context) {
      // Create gbif map layout
      var map = L.map("gbif-map").setView([5.4265362, 43.420024], 1);
      L.tileLayer(
        "https://tile.gbif.org/3857/omt/{z}/{x}/{y}@1x.png?style={style}",
        {
          attribution:
            'Occurrence map of '+drupalSettings.crawler.gbif_sp_name+' | Imagery and data © <a href="https://gbif.org/">GBIF</a>',
          maxZoom: 18,
          style: "gbif-violet"
        }
      ).addTo(map);
      // Add the occurrence layer
      L.tileLayer(
        "https://api.gbif.org/v2/map/occurrence/density/{z}/{x}/{y}@1x.png?style={style}&taxonKey={key}",
        {
          key: drupalSettings.crawler.gbif,
          style: "classic.poly&bin=hex&hexPerTile=130"
        }
      ).addTo(map);
    }, //attach:function (context)
  };
})(jQuery, Drupal, drupalSettings);
