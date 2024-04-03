<?php

declare(strict_types=1);

namespace Drupal\oe_color_scheme\Plugin\Field\FieldWidget;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Color Scheme' widget.
 *
 * @FieldWidget(
 *   id = "oe_color_scheme_widget",
 *   label = @Translation("Color Scheme widget"),
 *   field_types = {
 *     "oe_color_scheme"
 *   }
 * )
 */
class ColorSchemeWidget extends WidgetBase {

  /**
   * Constructs a ColorSchemeWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\Core\Extension\ThemeExtensionList $themeExtensionList
   *   The theme extension list.
   */
  public function __construct(
    protected string $plugin_id,
    protected mixed $plugin_definition,
    protected FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    protected ThemeManagerInterface $themeManager,
    protected ThemeExtensionList $themeExtensionList
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('theme.manager'),
      $container->get('extension.list.theme')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $options = $this->getColorSchemeOptions();

    if (empty($options)) {
      $element['#markup'] = $this->t("Color scheme options need to be defined in your active theme's info file.");

      return $element;
    }

    $element['name'] = [
      '#type' => 'select',
      '#title' => $this->t('Color scheme'),
      '#options' => $options,
      '#default_value' => $items[$delta]->name ?? NULL,
      '#required' => $element['#required'],
      '#empty_value' => '',
    ];

    return $element;
  }

  /**
   * Gets the color scheme names.
   *
   * @return array
   *   The color scheme options.
   */
  protected function getColorSchemeOptions(): array {
    $theme_name = $this->themeManager->getActiveTheme()->getName();
    $theme_info = $this->themeExtensionList->getExtensionInfo($theme_name);

    if (!empty($theme_info['color_scheme'])) {
      // Parse the options to be more humanly readable.
      $keys = $theme_info['color_scheme'];

      $values = array_map(function ($key) {
        $name = str_replace('_', ' ', $key);

        return ucfirst($name);
      }, $keys);

      return array_combine($keys, $values);
    }

    return [];
  }

}
