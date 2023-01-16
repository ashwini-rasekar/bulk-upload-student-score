<?php

/**
 * @file
 * Contains score_entity.page.inc.
 *
 * Page callback for Score entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Score entity templates.
 *
 * Default template: score_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_score_entity(array &$variables) {
  // Fetch ScoreEntity Entity Object.
  $score_entity = $variables['elements']['#score_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
