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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
    $a = $this->view->getExecutable();
      $b = $a->executeDisplay($this->displayId);
      $c = drupal_render($b);
    $a = 3;
  }

}