<?php

namespace Drupal\upload_details\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Drupal\upload_details\Entity\student;
use Drupal\upload_details\UploadDetailsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UploadDetails.
 */
class UploadDetails extends FormBase
{
  /**
   * @var $upload_details_service \Drupal\upload_details\UploadDetailsService
   */
  protected $upload_details_service;
  /**
   * @param \Drupal\upload_details\UploadDetailsService $upload_details_service
   */
  public function __constructor(UploadDetailsService $upload_details_service)
  {
    //$this->upload_details_service = \Drupal::service('upload_details.upload_student_details');
    $this->upload_details_service = $upload_details_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'upload_details';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Upload files'),
      //'#description' => $this->t('Fieldset to upload relevant files'),
      '#open' => True,
    ];
    $term_name = ['--Any--', 'Student', 'Score'];
    $form['fieldset']['upload_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Select category'),
      '#description' => $this->t('Select the option to upload relevant files'),
      '#options' => $term_name,
    ];
    $form['fieldset']['files'] = array(
      '#title' => t('Attach files'),
      '#type' => 'managed_file',
      '#required' => FALSE,
      '#upload_location' => 'public://upload_details/students',
      /*'#multiple' => TRUE,
      '#upload_validators' => array(
        'file_validate_extensions' => $this->getAllowedFileExtensions(),
      ),*/
      '#weight' => '0',
    );
    
    $form['fieldset']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $key = $form_state->getValue('upload_category');
    $val = $form['upload_category']['#options'][$key];

    $file = \Drupal::entityTypeManager()->getStorage('file')
      ->load($form_state->getValue('files')[0]);

    $full_path = $file->get('uri')->value;
    $file_name = basename($full_path);
    $inputFileName = \Drupal::service('file_system')->realpath('public://upload_details/students/' . $file_name);
    $new = \Drupal::service('upload_details.upload_student_details');
    if ($val == "Student") {
      $new->uploadStudentDetails($inputFileName, 1);
    } else {
      $new->uploadStudentDetails($inputFileName, 2);
    }
  }
  
}
