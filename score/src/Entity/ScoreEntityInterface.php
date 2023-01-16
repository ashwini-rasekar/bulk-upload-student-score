<?php

namespace Drupal\score\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Score entity entities.
 *
 * @ingroup score
 */
interface ScoreEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Score entity name.
   *
   * @return string
   *   Name of the Score entity.
   */
  public function getName();

  /**
   * Sets the Score entity name.
   *
   * @param string $name
   *   The Score entity name.
   *
   * @return \Drupal\score\Entity\ScoreEntityInterface
   *   The called Score entity entity.
   */
  public function setName($name);

  /**
   * Gets the Score entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Score entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Score entity creation timestamp.
   *
   * @param int $timestamp
   *   The Score entity creation timestamp.
   *
   * @return \Drupal\score\Entity\ScoreEntityInterface
   *   The called Score entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Score entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Score entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\score\Entity\ScoreEntityInterface
   *   The called Score entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Score entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Score entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\score\Entity\ScoreEntityInterface
   *   The called Score entity entity.
   */
  public function setRevisionUserId($uid);

}
