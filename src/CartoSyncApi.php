<?php

namespace Drupal\carto_sync;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Drupal\Core\Url;
use Drupal\ctools\ContextNotFoundException;
use GuzzleHttp\Client;
use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use MyProject\Proxies\__CG__\stdClass;

/**
 * Class CartoSyncApi.
 *
 * @package Drupal\carto_sync
 */
class CartoSyncApi implements CartoSyncApiInterface {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;
  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The CARTO user ID.
   *
   * @var string
   */
  protected $cartoId;

  /**
   * The CARTO API Key.
   *
   * @var string
   */
  protected $cartoApiKey;

  /**
   * Constructor.
   */
  public function __construct(Client $http_client, ConfigFactory $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;

    $this->cartoId = $this->configFactory->get('carto_sync.settings')->get('carto_id');
    $this->cartoApiKey = $this->configFactory->get('carto_sync.settings')->get('carto_api_key');
  }

  /**
   * {@inheritdoc}
   */
  public function datasetExists($dataset) {
    try {
      $this->getDatasetRows($dataset);
    }
    catch(CartoSyncException $exception) {
      if (preg_match('/^relation \"(.*)" does not exist$/', $exception->getMessage())) {
        return FALSE;
      }
      throw  $exception;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetRows($dataset) {
    $query = 'SELECT COUNT(*) FROM ' . $dataset;
    $result = $this->executeGetQuery($query);
    return $result->rows['0']->count;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetUrl($dataset) {
    return Url::fromUri('https://' . $this->cartoId . '.carto.com/dataset/' . $dataset);
  }

  /**
   *
   * @param $query
   * @return string
   */
  protected function buildUrl($query) {
    $options = [
      'query' => [
        'api_key' => $this->cartoApiKey,
        'q' => $query,
      ],
    ];
    return Url::fromUri('https://' . $this->cartoId . '.carto.com/api/v2/sql', $options)
      ->toString();
  }

  /**
   * @param $query
   * @return stdClass
   */
  protected function executeGetQuery($query) {
    $url = $this->buildUrl($query);
    try {
      $data = $this->httpClient->get($url);
      $output = json_decode($data->getBody());
    }
    catch (RequestException $e) {
      $error = json_decode($e->getResponse()->getBody()->getContents());
      throw new CartoSyncException($error->error[0]);
    }
    return $output;
  }

}
