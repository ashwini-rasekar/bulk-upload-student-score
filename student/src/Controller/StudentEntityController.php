<?php

namespace Drupal\student\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\student\Entity\StudentEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StudentEntityController.
 *
 *  Returns responses for Student entity routes.
 */
class StudentEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Student entity revision.
   *
   * @param int $student_entity_revision
   *   The Student entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($student_entity_revision) {
    $student_entity = $this->entityTypeManager()->getStorage('student_entity')
      ->loadRevision($student_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('student_entity');

    return $view_builder->view($student_entity);
  }

  /**
   * Page title callback for a Student entity revision.
   *
   * @param int $student_entity_revision
   *   The Student entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($student_entity_revision) {
    $student_entity = $this->entityTypeManager()->getStorage('student_entity')
      ->loadRevision($student_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $student_entity->label(),
      '%date' => $this->dateFormatter->format($student_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Student entity.
   *
   * @param \Drupal\student\Entity\StudentEntityInterface $student_entity
   *   A Student entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(StudentEntityInterface $student_entity) {
    $account = $this->currentUser();
    $student_entity_storage = $this->entityTypeManager()->getStorage('student_entity');

    $langcode = $student_entity->language()->getId();
    $langname = $student_entity->language()->getName();
    $languages = $student_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $student_entity->label()]) : $this->t('Revisions for %title', ['%title' => $student_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all student entity revisions") || $account->hasPermission('administer student entity entities')));
    $delete_permission = (($account->hasPermission("delete all student entity revisions") || $account->hasPermission('administer student entity entities')));

    $rows = [];

    $vids = $student_entity_storage->revisionIds($student_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\student\Entity\StudentEntityInterface $revision */
      $revision = $student_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $student_entity->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.student_entity.revision', [
            'student_entity' => $student_entity->id(),
            'student_entity_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $student_entity->toLink($date)->toString();
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
              Url::fromRoute('entity.student_entity.translation_revert', [
                'student_entity' => $student_entity->id(),
                'student_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.student_entity.revision_revert', [
                'student_entity' => $student_entity->id(),
                'student_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.student_entity.revision_delete', [
                'student_entity' => $student_entity->id(),
                'student_entity_revision' => $vid,
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

    $build['student_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
