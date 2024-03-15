<?php

declare(strict_types=1);

namespace Drupal\oe_color_scheme\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'Color Scheme' field type.
 *
 * @FieldType(
 *   id = "oe_color_scheme",
 *   label = @Translation("Color Scheme"),
 *   module = "oe_color_scheme",
 *   description = @Translation("Stores a colour scheme name."),
 *   default_widget = "oe_color_scheme_widget"
 * )
 */
class ColorSchemeItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'name' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'format' => ['name'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['name'] = DataDefinition::create('string')
      ->setLabel(t('Color Scheme'));

    return $properties;
  }

}
