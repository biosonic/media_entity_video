<?php

namespace Drupal\media_entity_video\Plugin\MediaEntity\Type;

use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides media type plugin for Video.
 *
 * @MediaType(
 *   id = "video",
 *   label = @Translation("Video"),
 *   description = @Translation("Provides business logic and metadata for Video Files.")
 * )
 */
class Video extends MediaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return [
      'mime' => $this->t('File MIME'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    $source_field = $this->configuration['source_field'];

    // Get the file document.
    /** @var \Drupal\file\FileInterface $file */
    $file = $media->{$source_field}->entity;

    // Return the field.
    switch ($name) {
      case 'mime':
        return !$file->filemime->isEmpty() ? $file->getMimeType() : FALSE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $form_state->getFormObject()->getEntity();
    $options = [];
    $allowed_field_types = ['file', 'video'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field with source information'),
      '#description' => $this->t('Field on media entity that stores Video file. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    $options_image = [0 => " - disabled - "];
    $allowed_field_types_thumbnail = ['image'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types_thumbnail) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options_image[$field_name] = $field->getLabel();
      }
    }

    $form['thumbnail_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field width thumbnail image'),
      '#description' => $this->t('Image Field on media entity that provide Thumbnail image. This field provider value is optional. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['thumbnail_field']) ? 0 : $this->configuration['thumbnail_field'],
      '#options' => $options_image,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return $this->config->get('icon_base') . '/video.png';
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {

    $thumbnail_field = $this->configuration['thumbnail_field'];
    if (!empty($thumbnail_field) && $file = $media->{$thumbnail_field}->entity) {
      return $file->getFileUri();
    }

    return $this->getDefaultThumbnail();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultName(MediaInterface $media) {
    // The default name will be the filename of the source_field, if present.
    $source_field = $this->configuration['source_field'];

    /** @var \Drupal\file\FileInterface $file */
    if (!empty($source_field) && ($file = $media->{$source_field}->entity)) {
      return $file->getFilename();
    }

    return parent::getDefaultName($media);
  }

}
