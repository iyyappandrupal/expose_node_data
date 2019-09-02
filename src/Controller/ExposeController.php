<?php

namespace Drupal\expose_node_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\expose_node_data\RestExport;

/**
 * Defines HelloController class.
 */
class ExposeController extends ControllerBase {

  /**
   * Construct Expose Controller Object.
   *
   * @param Drupal\expose_node_data\RestExport $rest_export
   *   The rest export object.
   */
  public function __construct(RestExport $rest_export) {
    $this->rest_export = $rest_export;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('rest.export')
    );
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function renderApi() {
    return $this->rest_export->getResults();
  }

}
