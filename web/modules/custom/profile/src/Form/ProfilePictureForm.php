<?php

namespace Drupal\profile\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ProfilePictureForm extends FormBase {

  public function getFormId() {
    return 'profile_picture_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $session = \Drupal::request()->getSession();
    $user_data = $session->get('api_redirect_result') ?? [];

    $form['#attributes']['enctype'] = 'multipart/form-data';
    $form['#method'] = 'post';
    $form['#action'] = '';

    $form['profile_picture_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'profile-form-wrapper'],
    ];

    $form['profile_picture_wrapper']['profile_picture'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['avatar', 'flex', 'flex-col', 'items-center', 'relative'],
      ],
    ];

    $profile_pic = !empty($user_data['profilePic'])
      ? htmlspecialchars($user_data['profilePic'], ENT_QUOTES, 'UTF-8')
      : '/themes/custom/engage_theme/images/Profile/profile_pic.png';

    $form['profile_picture_wrapper']['profile_picture']['image'] = [
      '#type' => 'markup',
      '#markup' => '
        <div class="w-28 rounded-full mb-3 aspect-square block overflow-hidden">
          <img src="' . $profile_pic . '" class="h-full w-full object-cover profilePicSrc" alt="Profile Image">
        </div>',
    ];

    $form['profile_picture_wrapper']['profile_picture']['user_id'] = [
      '#type' => 'markup',
      '#markup' => '<div class="userId_no"><p class="text-[13px] text-[#646262]">User Id :- ' . ($user_data['userId'] ?? '') . '</p></div>',
    ];

    $form['profile_picture_wrapper']['profile_picture']['edit_label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => 'Edit Profile Picture',
      '#attributes' => [
        'for' => 'profilePic',
        'class' => ['text-sm', 'font-bold', 'font-[Open_Sans]', 'border-2', 'px-4', 'cursor-pointer', 'translateLabel'],
        'label-alias' => 'la_edit_profile_picture',
      ],
    ];

    $form['upload_file'] = [
      '#type' => 'file',
      '#attributes' => [
        'onchange' => "fileUpload(this, 'profilePic')",
        'class' => ['form-control', 'profilePic', 'invisible', 'hidden'],
        'id' => 'profilePic',
        'accept' => 'image/*',
      ],
      '#name' => 'upload_file',
    ];

    $form['profilePic_filename'] = [
      '#type' => 'hidden',
      '#attributes' => ['class' => ['form-control', 'profilePic_name'], 'id' => 'profilePic_name'],
      '#name' => 'profilePic_filename',
    ];

    $form['profile_picture_wrapper']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#submit' => [],
      '#ajax' => [
        'callback' => '::ajaxRemoveCallback',
        'wrapper' => 'profile-form-wrapper',
        'effect' => 'fade',
      ],
      '#attributes' => [
        'id' => 'remove-profile-button',
        'class' => [
          'removeImg',
          'text-sm',
          'font-bold',
          'font-[Open_Sans]',
          'border-2',
          'px-4',
          'py-0.5',
          'cursor-pointer',
          'translateLabel',
        ],
      ],
    ];

    $form['note'] = [
      '#type' => 'markup',
      '#markup' => '<p class="flex justify-center text-[#a0a0a0] supportP text-sm font-[Open_Sans] font-bold my-4">(Supported file types: JPEG, PNG & file size limit is 2MB)</p>',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No-op: form uses AJAX-only actions.
  }

  public function ajaxRemoveCallback(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('profile_picture_form')->debug('AJAX Remove callback triggered.');

    $form['profile_picture_wrapper']['profile_picture']['image']['#markup'] = '
      <div class="w-28 rounded-full mb-3 aspect-square block overflow-hidden">
        <img src="/themes/custom/engage_theme/images/Profile/profile_pic.png" class="h-full w-full object-cover profilePicSrc" alt="Default Image">
      </div>';

    $form['profile_picture_wrapper']['profile_picture']['profilePic_filename']['#value'] = '';

    \Drupal::messenger()->addMessage('Profile picture removed.');

    return $form['profile_picture_wrapper'];
  }
}
