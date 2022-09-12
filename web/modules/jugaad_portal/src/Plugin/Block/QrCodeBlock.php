<?php

namespace Drupal\jugaad_portal\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Provides a qr code block.
 *
 * @Block(
 *   id = "jugaad_portal_qr_code",
 *   admin_label = @Translation("QR Code"),
 *   category = @Translation("Jugaad Portal")
 * )
 */
class QrCodeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'foo' => $this->t('Hello world!'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $current_path = \Drupal::service('path.current')->getPath();
    $current_path_alias = \Drupal::service('path_alias.manager')
      ->getAliasByPath($current_path);
    $file_path = \Drupal::service('file_url_generator')
      ->generate('public://qrcode.png');

    $writer = new PngWriter();

    // Create QR code
    $qrCode = QrCode::create($host . $current_path_alias)
      ->setEncoding(new Encoding('UTF-8'))
      ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
      ->setSize(300)
      ->setMargin(10)
      ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
      ->setForegroundColor(new Color(0, 0, 0))
      ->setBackgroundColor(new Color(255, 255, 255));

    $result = $writer->write($qrCode, null, null);

    $resultString = $result->getString();

    $file_system = \Drupal::service('file_system');
    $file_system->saveData($resultString, 'public://qrcode.png', FileSystemInterface::EXISTS_REPLACE);

    $build['content'] = [
      '#markup' => '<img src="' . $file_path->toString() . '" alt="barcode"   />',
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
