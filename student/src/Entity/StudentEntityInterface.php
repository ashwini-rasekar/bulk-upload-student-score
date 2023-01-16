<?php

namespace Drupal\student\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Student entity entities.
 *
 * @ingroup student
 */
interface StudentEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Student entity name.
   *
   * @return string
   *   Name of the Student entity.
   */
  public function getName();

  /**
   * Sets the Student entity name.
   *
   * @param string $name
   *   The Student entity name.
   *
   * @return \Drupal\student\Entity\StudentEntityInterface
   *   The called Student entity entity.
   */
  public function setName($name);

  /**
   * Gets the Student entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Student entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Student entity creation timestamp.
   *
   * @param int $timestamp
   *   The Student entity creation timestamp.
   *
   * @return \Drupal\student\Entity\StudentEntityInterface
   *   The called Student entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Student entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Student entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\student\Entity\StudentEntityInterface
   *   The called Student entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Student entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Student entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\student\Entity\StudentEntityInterface
   *   The called Student entity entity.
   */
  public function setRevisionUserId($uid);

}
