<?php

namespace Drupal\score;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Score entity entities.
 *
 * @ingroup score
 */
class ScoreEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Score entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\score\Entity\ScoreEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.score_entity.edit_form',
      ['score_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
