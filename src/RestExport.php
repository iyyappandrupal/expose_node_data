<?php

namespace Drupal\expose_node_data;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines HelloController class.
 */
class RestExport {

  /**
   * The custom message.
   *
   * @var message
   */
  protected $message = 'Access Denied';

  /**
   * Contructs a RestExport serice object.
   *
   * @param Symfony\Component\Serialization\Serializer $serializer
   *   The serializer object.
   * @param Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The current route object.
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   */
  public function __construct(Serializer $serializer, CurrentRouteMatch $route_match, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entity_type_manager) {
    $this->serializer = $serializer;
    $this->routematch = $route_match;
    $this->configfactory = $configFactory;
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * Validate API key.
   */
  private function validateApiKey($siteapikey) {
    // Get existing site information configuration.
    $config = $this->configfactory->get('system.site');
    return $siteapikey == $config->get('siteapikey') ? TRUE : FALSE;
  }

  /**
   * Validate Node ID.
   */
  private function validateNodeId($nid, $type) {
    $node = $this->entity_type_manager->getStorage('node')
      ->loadByProperties(['type' => $type, 'nid' => $nid]);
    return reset($node) ? reset($node) : NULL;
  }

  /**
   * Retuns the json response.
   *
   * Default parameter $type = page
   * We can expose node values of other types using
   * rest.export service in future by passing type paramter.
   */
  public function getResults($type = ['page']) {
    $parameters = $this->routematch->getParameters();
    $siteapikey = $parameters->get('siteapikey');
    $nid = $parameters->get('node');
    return $this->exposeResults($nid, $siteapikey, $type);
  }

  /**
   * Convert the node values to json.
   */
  private function exposeResults($nid, $siteapikey, $type) {
    if ($this->validateApiKey($siteapikey) && $node = $this->validateNodeId($nid, $type)) {
      $data = $this->serializer->serialize($node, 'json', ['plugin_id' => 'entity']);
      $data = $this->serializer->decode($data, 'json');
      return new JsonResponse([
        'data' => $data,
      ]);
    }
    else {
      return new JsonResponse([
        'data' => $this->message,
      ]);
    }
  }

}
