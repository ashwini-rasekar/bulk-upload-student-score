<?php

namespace Drupal\student\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Student entity entity.
 *
 * @ingroup student
 *
 * @ContentEntityType(
 *   id = "student_entity",
 *   label = @Translation("Student entity"),
 *   bundle_label = @Translation("Student entity type"),
 *   handlers = {
 *     "storage" = "Drupal\student\StudentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\student\StudentEntityListBuilder",
 *     "views_data" = "Drupal\student\Entity\StudentEntityViewsData",
 *     "translation" = "Drupal\student\StudentEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\student\Form\StudentEntityForm",
 *       "add" = "Drupal\student\Form\StudentEntityForm",
 *       "edit" = "Drupal\student\Form\StudentEntityForm",
 *       "delete" = "Drupal\student\Form\StudentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\student\StudentEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\student\StudentEntityAccessControlHandler",
 *   },
 *   base_table = "student_entity",
 *   data_table = "student_entity_field_data",
 *   revision_table = "student_entity_revision",
 *   revision_data_table = "student_entity_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer student entity entities",
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
 *     "canonical" = "/admin/structure/student_entity/{student_entity}",
 *     "add-page" = "/admin/structure/student_entity/add",
 *     "add-form" = "/admin/structure/student_entity/add/{student_entity_type}",
 *     "edit-form" = "/admin/structure/student_entity/{student_entity}/edit",
 *     "delete-form" = "/admin/structure/student_entity/{student_entity}/delete",
 *     "version-history" = "/admin/structure/student_entity/{student_entity}/revisions",
 *     "revision" = "/admin/structure/student_entity/{student_entity}/revisions/{student_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/student_entity/{student_entity}/revisions/{student_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/student_entity/{student_entity}/revisions/{student_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/student_entity/{student_entity}/revisions/{student_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/student_entity",
 *   },
 *   bundle_entity_type = "student_entity_type",
 *   field_ui_base_route = "entity.student_entity_type.edit_form"
 * )
 */
class StudentEntity extends EditorialContentEntityBase implements StudentEntityInterface {

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
    // make the student_entity owner the revision author.
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
      ->setDescription(t('The user ID of author of the Student entity entity.'))
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
      ->setDescription(t('The name of the Student entity entity.'))
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

      $fields['class'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Class'))
      ->setDescription(t('The class of the Student entity entity.'))
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

      $fields['roll_number'] = BaseFieldDefinition::create('integer')
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

     $fields['contact_number'] = BaseFieldDefinition::create('string')
     ->setLabel(t('Contact Number'))
     ->setDescription(t('Contact Number of the Student'))
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
     
    $fields['status']->setDescription(t('A boolean indicating whether the Student entity is published.'))
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
