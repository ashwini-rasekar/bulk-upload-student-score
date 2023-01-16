<?php

namespace Drupal\score\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Score entity type entity.
 *
 * @ConfigEntityType(
 *   id = "score_entity_type",
 *   label = @Translation("Score entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\score\ScoreEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\score\Form\ScoreEntityTypeForm",
 *       "edit" = "Drupal\score\Form\ScoreEntityTypeForm",
 *       "delete" = "Drupal\score\Form\ScoreEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\score\ScoreEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   config_prefix = "score_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "score_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/score_entity_type/{score_entity_type}",
 *     "add-form" = "/admin/structure/score_entity_type/add",
 *     "edit-form" = "/admin/structure/score_entity_type/{score_entity_type}/edit",
 *     "delete-form" = "/admin/structure/score_entity_type/{score_entity_type}/delete",
 *     "collection" = "/admin/structure/score_entity_type"
 *   }
 * )
 */
class ScoreEntityType extends ConfigEntityBundleBase implements ScoreEntityTypeInterface {

  /**
   * The Score entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Score entity type label.
   *
   * @var string
   */
  protected $label;

}
