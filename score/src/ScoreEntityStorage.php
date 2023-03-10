<?php

namespace Drupal\score;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class ScoreEntityStorage extends SqlContentEntityStorage implements ScoreEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ScoreEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {score_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {score_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ScoreEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {score_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('score_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
