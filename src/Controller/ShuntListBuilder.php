<?php

/**
 * @file
 * Contains \Drupal\shunt\Controller\ShuntListBuilder.
 */

namespace Drupal\shunt\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\shunt\Entity\ShuntInterface;

/**
 * Provides a listing of shunts.
 */
class ShuntListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Shunt');
    $header['description'] = array(
      'data' => $this->t('Description'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['description'] = $this->getDescription($entity);
    $row['status'] = $this->getStatus($entity);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->isShuntEnabled()) {
      $operations['disable'] = array(
        'title' => t('Disable'),
        'weight' => -10,
        'url' => $entity->urlInfo('disable'),
      );
    }
    else {
      $operations['enable'] = array(
        'title' => t('Enable'),
        'weight' => -10,
        'url' => $entity->urlInfo('enable'),
      );
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = $this->t('No shunts available.');
    return $build;
  }

  /**
   * Returns the escaped description of a given shunt.
   *
   * @param \Drupal\shunt\Entity\ShuntInterface $shunt
   *   A shunt entity.
   *
   * @return string
   *   The escaped shunt description.
   */
  protected function getDescription(ShuntInterface $shunt) {
    return Xss::filterAdmin($shunt->getDescription());
  }

  /**
   * Returns the human-readable status of a shunt.
   *
   * @param ShuntInterface $shunt
   *   A shunt entity.
   *
   * @return string
   *   The human-readable shunt status.
   */
  protected function getStatus(ShuntInterface $shunt) {
    return ($shunt->isShuntEnabled()) ? t('Enabled') : t('Disabled');
  }

}
