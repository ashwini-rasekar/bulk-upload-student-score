<?php

namespace Drupal\upload_details;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Drupal\Core\Database\Connection;
use Drupal\customModule\Entity\StudentEntity;


/**
 * Class UploadDetailsService.
 */
class UploadDetailsService
{

  /**
   * Constructs a new UploadDetailsService object.
   */
  protected $database;
  public function __construct(Connection $connection)
  {
    $this->database = $connection;
  }
  public function uploadStudentDetails($inputFileName, $value)
  {
    $spreadsheet = IOFactory::load($inputFileName);

    $sheetData = $spreadsheet->getActiveSheet();

    //$rows = array();
    foreach ($sheetData->getRowIterator() as $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(FALSE);
      $cells = [];
      foreach ($cellIterator as $cell) {
        $cells[] = $cell->getValue();
      }
      $rows[] = $cells;
    }
    if ($value == 1) {
      $table_name = 'student_entity_field_data';
    } else {
      $table_name = 'score_entity_field_data';
    }
    $query = $this->database->select($table_name, 'std');
    $query->fields('std', ['id']);
    $result = count($query->execute()->fetchAll());

    if ($value == 1) {

      foreach ($rows as $row) {
        $this->database->insert('student_entity_field_data')
          ->fields(array(
            'id' => $result,
            'vid' => $result,
            'type' => 'students_entity',
            'langcode' => 'en',
            'status' => 1,
            'user_id' => 1,
            'name' => $row[0],
            'class' => $row[1],
            'roll_number' => $row[2],
            'contact_number' => $row[3],
            'created' => time(),
            'changed' => time(),
            'revision_translation_affected' => 1,
            'default_langcode' => 1
          ))
          ->execute();

        $result++;
      }
    } else {
      foreach ($rows as $row) {
        $this->database->insert('score_entity_field_data')
          ->fields(array(
            'id' => $result,
            'vid' => $result,
            'type' => 'score_entity',
            'langcode' => 'en',
            'status' => 1,
            'subject' => $row[0],
            'roll_number_score' => $row[1],
            'score' => $row[2],
            'created' => time(),
            'changed' => time(),
            'revision_translation_affected' => 1,
            'default_langcode' => 1
          ))
          ->execute();

        $result++;
      }
    }

    // Display result.

    \Drupal::messenger()->addMessage('Imported successfully');
  }
}
