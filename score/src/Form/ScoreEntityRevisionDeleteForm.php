<?php

namespace Drupal\score\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Score entity revision.
 *
 * @ingroup score
 */
class ScoreEntityRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Score entity revision.
   *
   * @var \Drupal\score\Entity\ScoreEntityInterface
   */
  protected $revision;

  /**
   * The Score entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $scoreEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->scoreEntityStorage = $container->get('entity_type.manager')->getStorage('score_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'score_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.score_entity.version_history', ['score_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $score_entity_revision = NULL) {
    $this->revision = $this->ScoreEntityStorage->loadRevision($score_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->ScoreEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Score entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Score entity %title has been deleted.', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.score_entity.canonical',
       ['score_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {score_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.score_entity.version_history',
         ['score_entity' => $this->revision->id()]
      );
    }
  }

}
