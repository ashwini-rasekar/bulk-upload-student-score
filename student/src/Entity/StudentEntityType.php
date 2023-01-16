<?php

namespace Drupal\student\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Student entity type entity.
 *
 * @ConfigEntityType(
 *   id = "student_entity_type",
 *   label = @Translation("Student entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\student\StudentEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\student\Form\StudentEntityTypeForm",
 *       "edit" = "Drupal\student\Form\StudentEntityTypeForm",
 *       "delete" = "Drupal\student\Form\StudentEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\student\StudentEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   config_prefix = "student_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "student_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/student_entity_type/{student_entity_type}",
 *     "add-form" = "/admin/structure/student_entity_type/add",
 *     "edit-form" = "/admin/structure/student_entity_type/{student_entity_type}/edit",
 *     "delete-form" = "/admin/structure/student_entity_type/{student_entity_type}/delete",
 *     "collection" = "/admin/structure/student_entity_type"
 *   }
 * )
 */
class StudentEntityType extends ConfigEntityBundleBase implements StudentEntityTypeInterface {

  /**
   * The Student entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Student entity type label.
   *
   * @var string
   */
  protected $label;

}
