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
   * Tests the color scheme requirements.
   *
   * @dataProvider colorSchemeRequirementsDataProvider
   */
  public function testColorSchemeRequirements(string $theme, int $severity, string $value) {
    $this->installDefaultTheme($theme);
    \Drupal::moduleHandler()->loadInclude('oe_color_scheme', 'install');

    $requirements = oe_color_scheme_requirements('runtime');

    $this->assertEquals($severity, $requirements['oe_color_scheme']['severity']);
    $this->assertEquals($value, $requirements['oe_color_scheme']['value']);
  }

  /**
   * Data provider for testColorSchemeRequirements.
   *
   * @return \Generator
   *   The test data.
   */
  protected function colorSchemeRequirementsDataProvider(): \Generator {
    yield [
      'theme' => 'oe_color_scheme_test_theme_no_values',
      'severity' => 2,
      'value' => 'Not defined in active theme info file.',
    ];
    yield [
      'theme' => 'oe_color_scheme_test_theme_invalid',
      'severity' => 2,
      'value' => 'Invalid key/value pair in the color_scheme options.',
    ];
    yield [
      'theme' => 'oe_color_scheme_test_theme',
      'severity' => 0,
      'value' => 'Defined in active theme info file.',
    ];
  }

  /**
   * Tests the color scheme widget with scheme values.
   */
  public function testColorSchemeWidget(): void {
    $this->installDefaultTheme('oe_color_scheme_test_theme');
    $form = $this->buildEntityTestForm();

    $this->assertEquals('Color scheme', $form['field_colorscheme']['widget']['0']['name']['#title']);
    $expected = [
      '' => '- None -',
      'pixy-dust' => 'Pixie Dust',
      'powder-puff' => 'Powder Puff',
      'lagunablue' => 'Laguna <b>Blue</b>',
    ];

    $this->assertEquals($expected, $form['field_colorscheme']['widget']['0']['name']['#options']);
  }

  /**
   * Tests the ColorSchemeItem.
   */
  public function testColorSchemeItem(): void {
    $values = [
      'field_colorscheme' => [
        'name' => 'foo_bar',
      ],
    ];

    $entity = EntityTest::create($values);
    $entity->save();

    $entity = $this->reloadEntity($entity);

    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    $field_storage = $entity->getFieldDefinitions()['field_colorscheme']
      ->getFieldStorageDefinition();

    $properties = $field_storage->getPropertyDefinitions();

    $this->assertArrayHasKey('name', $properties);
    $this->assertEquals('string', $properties['name']->getDataType());

    $schema = $field_storage->getSchema();

    $this->assertArrayHasKey('name', $schema['columns']);
    $this->assertEquals('varchar', $schema['columns']['name']['type']);
    $this->assertEquals(255, $schema['columns']['name']['length']);
    $this->assertEquals(['name'], $schema['indexes']['format']);

    $this->assertEquals('foo_bar', $entity->get('field_colorscheme')->getValue()['0']['name']);
  }

  /**
   * Tests that the entity build contains the color scheme info if needed.
   */
  public function testEntityBuild(): void {
    $values = [
      'field_colorscheme' => [
        'name' => 'foo_bar',
      ],
    ];

    $entity = EntityTest::create($values);
    $entity->save();

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = $this->container->get('renderer');

    $entity_view_builder = $this->container->get('entity_type.manager')->getViewBuilder('entity_test');
    $build = $entity_view_builder->view($entity);
    $rendered = (string) $renderer->renderRoot($build);

    // Assert the value is present in the build but not in the rendered output.
    $this->assertSame('foo_bar', $build['#oe_color_scheme']);
    $this->assertStringNotContainsString('foo_bar', $rendered);

    $values = [
      'field_colorscheme' => [
        'name' => '',
      ],
    ];

    $entity = EntityTest::create($values);
    $entity->save();

    $build = $entity_view_builder->view($entity);
    $rendered = (string) $renderer->renderRoot($build);

    // Assert the value is not present.
    $this->assertArrayNotHasKey('#oe_color_scheme', $build);
    $this->assertStringNotContainsString('foo_bar', $rendered);
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
