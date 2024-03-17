<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_color_scheme\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormState;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the Color Scheme field.
 */
class ColorSchemeTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_color_scheme',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $entity = EntityTest::create();

    FieldStorageConfig::create([
      'field_name' => 'field_colorscheme',
      'entity_type' => 'entity_test',
      'type' => 'oe_color_scheme',
    ])->save();

    FieldConfig::create([
      'label' => 'ColorScheme field',
      'field_name' => 'field_colorscheme',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    $entity_form_display = EntityFormDisplay::collectRenderDisplay($entity, 'default');
    $entity_form_display->setComponent('field_colorscheme', [
      'region' => 'content',
      'type' => 'oe_color_scheme_widget',
    ]);
    $entity_form_display->save();
  }

  /**
   * Tests the color scheme widget when no scheme values are set.
   */
  public function testColorSchemeWidgetNoValues(): void {
    $this->installDefaultTheme('oe_color_scheme_test_theme_no_values');
    $form = $this->buildEntityTestForm();

    $this->assertEquals("Color scheme options need to be defined in your active theme's info file.", $form['field_colorscheme']['widget']['0']['#markup']);
  }

  /**
   * Tests the color scheme widget with scheme values.
   */
  public function testColorSchemeWidget(): void {
    $this->installDefaultTheme('oe_color_scheme_test_theme');
    $form = $this->buildEntityTestForm();

    $this->assertEquals('Color scheme', $form['field_colorscheme']['widget']['0']['name']['#title']);
    $expected = [
      'pixy_dust' => 'Pixy dust',
      'powder_puff' => 'Powder puff',
    ];
    $this->assertEquals($expected, $form['field_colorscheme']['widget']['0']['name']['#options']);
  }

  /**
   * Installs a theme and sets it as default.
   *
   * @param string $theme_name
   *   The theme's machine name.
   */
  protected function installDefaultTheme(string $theme_name): void {
    $this->container->get('theme_installer')->install([$theme_name]);
    $this->config('system.theme')->set('default', $theme_name)->save();
  }

  /**
   * Builds a form using the test entity.
   *
   * @return array
   *   The built form.
   */
  protected function buildEntityTestForm(): array {
    $entity = EntityTest::create();
    $form_object = $this->container->get('entity_type.manager')->getFormObject('entity_test', 'default');
    $form_object->setEntity($entity);
    $form_state = new FormState();

    return $this->container->get('form_builder')->buildForm($form_object, $form_state);
  }

}
