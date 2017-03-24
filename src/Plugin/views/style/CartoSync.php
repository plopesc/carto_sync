<?php

namespace Drupal\carto_sync\Plugin\views\style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Defines a style plugin for CARTO Sync.
 *
 * @ViewsStyle(
 *   id = "carto_sync",
 *   theme = "views_view_unformatted",
 *   title = @Translation("CartoSync"),
 *   help = @Translation("CartoSync."),
 *   display_types = {"carto_sync"}
 * )
 */
class CartoSync extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = FALSE;

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;


  /**
   * Render the given style.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Note: views UI registers this theme handler on our behalf. Your module
    // will have to register your theme handlers if you do stuff like this.
    $form['#theme'] = 'carto_sync_style_plugin_table';

    // Create an array of allowed columns from the data we know:
    $field_names = $this->displayHandler->getFieldLabels();

    $columns = $this->sanitizeColumns($this->options['columns']);
    $geo_columns = $this->geoColumnsofType('geofield');
    $int_columns = $this->geoColumnsofType('integer');

    foreach ($columns as $field => $column) {

      // Markup for the field name
      $form['info'][$field]['name'] = [
        '#markup' => $field_names[$field],
      ];

      if (isset($this->options['primary_key'])) {
        $default = $this->options['primary_key'];
        if (!isset($columns[$default])) {
          $default = -1;
        }
      }
      else {
        $default = -1;
      }

      if (isset($this->options['the_geom'])) {
        $the_geom = $this->options['the_geom'];
        if (!isset($columns[$the_geom])) {
          $the_geom = -1;
        }
      }
      else {
        $the_geom = -1;
      }

      $form['info'][$field]['field_name'] = [
        '#title' => $this->t('Separator for @field', ['@field' => $field]),
        '#title_display' => 'invisible',
        '#type' => 'textfield',
        '#size' => 10,
        '#default_value' => isset($this->options['info'][$field]['field_name']) ? $this->options['info'][$field]['field_name'] : $field,
        '#required' => TRUE,
      ];

      if (in_array($field, $int_columns)) {
        // Provide an ID so we can have such things.
        $radio_id = Html::getUniqueId('edit-default-' . $field);
        $form['primary_key'][$field] = [
          '#title' => $this->t('Default sort for @field', ['@field' => $field]),
          '#title_display' => 'invisible',
          '#type' => 'radio',
          '#return_value' => $field,
          '#parents' => ['style_options', 'primary_key'],
          '#id' => $radio_id,
          // because 'radio' doesn't fully support '#id' =(
          '#attributes' => ['id' => $radio_id],
          '#default_value' => $default,
        ];
      }

      if (in_array($field, $geo_columns)) {
        // Provide an ID so we can have such things.
        $radio_id = Html::getUniqueId('edit-the-geom-' . $field);
        $form['the_geom'][$field] = [
          '#title' => $this->t('Default sort for @field', ['@field' => $field]),
          '#title_display' => 'invisible',
          '#type' => 'radio',
          '#return_value' => $field,
          '#parents' => ['style_options', 'the_geom'],
          '#id' => $radio_id,
          // because 'radio' doesn't fully support '#id' =(
          '#attributes' => ['id' => $radio_id],
          '#default_value' => $the_geom,
        ];
      }
    }

    $form['description_markup'] = [
      '#markup' => '<div class="js-form-item form-item description">' . $this->t('Select the CARTO column names to use. The Primary Key column is forced to be named cartob_id and has to be an integer. The Geometry one is forced to be named the_geom') . '</div>',
    ];

    return $form;

  }

  /**
   * Normalize a list of columns based upon the fields that are
   * available. This compares the fields stored in the style handler
   * to the list of fields actually in the view, removing fields that
   * have been removed and adding new fields in their own column.
   *
   * - Each field must be in a column.
   * - Each column must be based upon a field, and that field
   *   is somewhere in the column.
   * - Any fields not currently represented must be added.
   * - Columns must be re-ordered to match the fields.
   *
   * @param $columns
   *   An array of all fields; the key is the id of the field and the
   *   value is the id of the column the field should be in.
   * @param $fields
   *   The fields to use for the columns. If not provided, they will
   *   be requested from the current display. The running render should
   *   send the fields through, as they may be different than what the
   *   display has listed due to access control or other changes.
   *
   * @return array
   *    An array of all the sanitized columns.
   */
  public function sanitizeColumns($columns, $fields = NULL) {
    $sanitized = [];
    if ($fields === NULL) {
      $fields = $this->displayHandler->getOption('fields');
    }
    // Preconfigure the sanitized array so that the order is retained.
    foreach ($fields as $field => $info) {
      // Set to itself so that if it isn't touched, it gets column
      // status automatically.
      $sanitized[$field] = $field;
    }

    foreach ($columns as $field => $column) {
      // first, make sure the field still exists.
      if (!isset($sanitized[$field])) {
        continue;
      }

      // If the field is the column, mark it so, or the column
      // it's set to is a column, that's ok
      if ($field == $column || $columns[$column] == $column && !empty($sanitized[$column])) {
        $sanitized[$field] = $column;
      }
      // Since we set the field to itself initially, ignoring
      // the condition is ok; the field will get its column
      // status back.
    }

    return $sanitized;
  }

  /**
   * Returns the list of columns in the view.
   *
   * @param string $field_type
   *   The field type we are tyring to get data.
   *
   * @return string[]
   *   Field names of the requested field type.
   */
  protected function geoColumnsofType($field_type) {
    $geo_columns = [];
    foreach ($this->displayHandler->getHandlers('field') as $id => $handler) {
      if (isset($handler->definition['field_name'])) {
        $entity_type_id = $handler->definition['entity_type'];
        $def = \Drupal::entityManager()->getFieldStorageDefinitions($entity_type_id);
        if ($def[$handler->definition['field_name']]->getType() == $field_type) {
          $geo_columns[] = $id;
        }
      }
    }

    return  $geo_columns;
  }

}
