<?php

namespace Drupal\student;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Student entity entities.
 *
 * @ingroup student
 */
class StudentEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Student entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\student\Entity\StudentEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.student_entity.edit_form',
      ['student_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
