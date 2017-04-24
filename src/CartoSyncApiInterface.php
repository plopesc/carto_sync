<?php

namespace Drupal\carto_sync;

/**
 * Interface CartoSyncApiInterface.
 *
 * @package Drupal\carto_sync
 */
interface CartoSyncApiInterface {

  /**
   * Checks whether a dataset name exists in CARTO or not.
   *
   * @param string $dataset
   *  The dataset name.
   *
   * @return bool TRUE if the dataset name exists, otherwise FALSE.
   *  TRUE if the dataset name exists, otherwise FALSE.
   *
   * @throws CartoSyncException
   */
  public function datasetExists($dataset);

  /**
   * Retrieves the number of rows in a given CARTO dataset name.
   *
   * @param string $dataset
   *  The dataset name.
   *
   * @return int
   *  Integer indicating the number of rows in the given dataset.
   */
  public function getDatasetRows($dataset);

  /**
   * Generates the CARTO admin dataset URL.
   * @param string $dataset
   *  The dataset name.
   * @return Url
   *  URL object to the given dataset.
   */
  public function getDatasetUrl($dataset);

}
