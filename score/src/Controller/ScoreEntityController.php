<?php

namespace Drupal\score\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\score\Entity\ScoreEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ScoreEntityController.
 *
 *  Returns responses for Score entity routes.
 */
class ScoreEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Score entity revision.
   *
   * @param int $score_entity_revision
   *   The Score entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($score_entity_revision) {
    $score_entity = $this->entityTypeManager()->getStorage('score_entity')
      ->loadRevision($score_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('score_entity');

    return $view_builder->view($score_entity);
  }

  /**
   * Page title callback for a Score entity revision.
   *
   * @param int $score_entity_revision
   *   The Score entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($score_entity_revision) {
    $score_entity = $this->entityTypeManager()->getStorage('score_entity')
      ->loadRevision($score_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $score_entity->label(),
      '%date' => $this->dateFormatter->format($score_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Score entity.
   *
   * @param \Drupal\score\Entity\ScoreEntityInterface $score_entity
   *   A Score entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ScoreEntityInterface $score_entity) {
    $account = $this->currentUser();
    $score_entity_storage = $this->entityTypeManager()->getStorage('score_entity');

    $langcode = $score_entity->language()->getId();
    $langname = $score_entity->language()->getName();
    $languages = $score_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $score_entity->label()]) : $this->t('Revisions for %title', ['%title' => $score_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all score entity revisions") || $account->hasPermission('administer score entity entities')));
    $delete_permission = (($account->hasPermission("delete all score entity revisions") || $account->hasPermission('administer score entity entities')));

    $rows = [];

    $vids = $score_entity_storage->revisionIds($score_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\score\Entity\ScoreEntityInterface $revision */
      $revision = $score_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $score_entity->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.score_entity.revision', [
            'score_entity' => $score_entity->id(),
            'score_entity_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $score_entity->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.score_entity.translation_revert', [
                'score_entity' => $score_entity->id(),
                'score_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.score_entity.revision_revert', [
                'score_entity' => $score_entity->id(),
                'score_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.score_entity.revision_delete', [
                'score_entity' => $score_entity->id(),
                'score_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['score_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
