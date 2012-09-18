<?php
// @file: theme-settings.php
function flux_form_system_theme_settings_alter(&$form, &$form_state) {
  // Container fieldset
  $form['cover_photo'] = array(
    '#type' => 'fieldset',
    '#title' => t('Cover Photo'),
  );
  
  // Default path for image
  $cover_photo_path = theme_get_setting('cover_photo_path');
  if (file_uri_scheme($cover_photo_path) == 'public') {
    $cover_photo_path = file_uri_target($cover_photo_path);
  }
  
  // Helpful text showing the file name, disabled to avoid the user thinking it can be used for any purpose.
  $form['cover_photo']['cover_photo_path'] = array(
    '#type' => 'textfield',
    '#title' => 'Path to image',
    '#default_value' => $cover_photo_path,
    '#prefix' => theme('image_style', array('style_name' => 'medium', 'path' => $cover_photo_path)),
    '#disabled' => TRUE,
  );

  // Upload field
  $form['cover_photo']['cover_photo_upload'] = array(
    '#type' => 'file',
    '#title' => 'Upload cover photo',
    '#description' => 'Upload a new image for your cover photo.',
  );

  // Attach custom submit handler to the form
  $form['#submit'][] = 'flux_settings_submit';
}

function flux_settings_submit($form, &$form_state) {
  $settings = array();
  // Get the previous value
  $previous = 'public://' . $form['cover_photo']['cover_photo_path']['#default_value'];
  
  $file = file_save_upload('cover_photo_upload');
  if ($file) {
    $parts = pathinfo($file->filename);
    $destination = 'public://' . $parts['basename'];
    $file->status = FILE_STATUS_PERMANENT;
    
    if(file_copy($file, $destination, FILE_EXISTS_REPLACE)) {
      $_POST['cover_photo_path'] = $form_state['values']['cover_photo_path'] = $destination;
      // If new file has a different name than the old one, delete the old
      if ($destination != $previous) {
        drupal_unlink($previous);
      }
    }
  } else {
    // Avoid error when the form is submitted without specifying a new image
    $_POST['cover_photo_path'] = $form_state['values']['cover_photo_path'] = $previous;
  }
  
}