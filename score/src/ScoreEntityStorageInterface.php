<?php

namespace Drupal\score;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\score\Entity\ScoreEntityInterface;

/**
 * Defines the storage handler class for Score entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Score entity entities.
 *
 * @ingroup score
 */
interface ScoreEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Score entity revision IDs for a specific Score entity.
   *
   * @param \Drupal\score\Entity\ScoreEntityInterface $entity
   *   The Score entity entity.
   *
   * @return int[]
   *   Score entity revision IDs (in ascending order).
   */
  public function revisionIds(ScoreEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Score entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Score entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\score\Entity\ScoreEntityInterface $entity
   *   The Score entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ScoreEntityInterface $entity);

  /**
   * Unsets the language for all Score entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
