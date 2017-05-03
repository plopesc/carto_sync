<?php

namespace Drupal\carto_sync\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportForm extends ConfirmFormBase {

  /**
   * @var ViewEntityInterface
   */
  protected $view;

  /**
   * @var array
   */
  protected $displayId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carto_sync_import';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to create the dataset %id?', array('%id' => $this->id));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('carto_sync.carto_sync_dashboard');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Views data will be imported into a CARTO dataset');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Import');
  }

  /**
   * {@inheritdoc}
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ViewEntityInterface $view = NULL, $display_id = NULL) {
    if (!$view->getDisplay($display_id)) {
      throw new NotFoundHttpException();
    }
    $this->view = $view;
    $this->displayId = $display_id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * @var $service \Drupal\carto_sync\CartoSyncApiInterface
     */
    $service = \Drupal::service('carto_sync.api');
    $dataset = $this->view->getDisplay($this->displayId)['display_options']['dataset_name'];
    if ($service->datasetExists($dataset)) {
      $form_state->setError($form['actions']['submit'], $this->t('Dataset @dataset already exists in your CARTO account.', ['@dataset' => $dataset));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $executable = $this->view->getExecutable();
    $imported = $executable->executeDisplay($this->displayId);
    if ($imported) {
      drupal_set_message('Success');
    }
    else {
      drupal_set_message('Fail', 'error');
    }
    $form_state->setRedirect('carto_sync.carto_sync_dashboard');
  }

}
