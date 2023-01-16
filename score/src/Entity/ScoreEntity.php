<?php

namespace Drupal\score\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Score entity entity.
 *
 * @ingroup score
 *
 * @ContentEntityType(
 *   id = "score_entity",
 *   label = @Translation("Score entity"),
 *   bundle_label = @Translation("Score entity type"),
 *   handlers = {
 *     "storage" = "Drupal\score\ScoreEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\score\ScoreEntityListBuilder",
 *     "views_data" = "Drupal\score\Entity\ScoreEntityViewsData",
 *     "translation" = "Drupal\score\ScoreEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\score\Form\ScoreEntityForm",
 *       "add" = "Drupal\score\Form\ScoreEntityForm",
 *       "edit" = "Drupal\score\Form\ScoreEntityForm",
 *       "delete" = "Drupal\score\Form\ScoreEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\score\ScoreEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\score\ScoreEntityAccessControlHandler",
 *   },
 *   base_table = "score_entity",
 *   data_table = "score_entity_field_data",
 *   revision_table = "score_entity_revision",
 *   revision_data_table = "score_entity_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer score entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
*   revision_metadata_keys = {
*     "revision_user" = "revision_uid",
*     "revision_created" = "revision_timestamp",
*     "revision_log_message" = "revision_log"
*   },
 *   links = {
 *     "canonical" = "/admin/structure/score_entity/{score_entity}",
 *     "add-page" = "/admin/structure/score_entity/add",
 *     "add-form" = "/admin/structure/score_entity/add/{score_entity_type}",
 *     "edit-form" = "/admin/structure/score_entity/{score_entity}/edit",
 *     "delete-form" = "/admin/structure/score_entity/{score_entity}/delete",
 *     "version-history" = "/admin/structure/score_entity/{score_entity}/revisions",
 *     "revision" = "/admin/structure/score_entity/{score_entity}/revisions/{score_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/score_entity/{score_entity}/revisions/{score_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/score_entity/{score_entity}/revisions/{score_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/score_entity/{score_entity}/revisions/{score_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/score_entity",
 *   },
 *   bundle_entity_type = "score_entity_type",
 *   field_ui_base_route = "entity.score_entity_type.edit_form"
 * )
 */
class ScoreEntity extends EditorialContentEntityBase implements ScoreEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the score_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Score entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Score entity entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
      $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setDescription(t('Enter subject value.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

      $fields['roll_number_score'] = BaseFieldDefinition::create('integer')
     ->setLabel(t('Roll Number'))
     ->setDescription(t('Roll Number of the Student'))
     ->setRevisionable(TRUE)
     ->setTranslatable(TRUE)
     ->setDisplayOptions('form', array(
       'type' => 'string_textfield',
       'settings' => array(
         'display_label' => TRUE,
       ),
     ))
    ->setDisplayOptions('view', array(
       'label' => 'hidden',
       'type' => 'string',
     ))
     ->setDisplayConfigurable('form', TRUE)
     ->setRequired(TRUE);   

     $fields['score'] = BaseFieldDefinition::create('integer')
     ->setLabel(t('Socre'))
     ->setDescription(t('Score of the Student'))
     ->setRevisionable(TRUE)
     ->setTranslatable(TRUE)
     ->setDisplayOptions('form', array(
       'type' => 'string_textfield',
       'settings' => array(
         'display_label' => TRUE,
       ),
     ))
    ->setDisplayOptions('view', array(
       'label' => 'hidden',
       'type' => 'string',
     ))
     ->setDisplayConfigurable('form', TRUE)
     ->setRequired(TRUE);   

    $fields['status']->setDescription(t('A boolean indicating whether the Score entity is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
