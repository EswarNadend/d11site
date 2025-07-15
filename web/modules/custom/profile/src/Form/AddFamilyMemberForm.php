<?php

namespace Drupal\profile\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
class AddFamilyMemberForm extends FormBase
{
  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  public function __construct(ClientInterface $http_client)
  {
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('http_client')
    );
  }
  public function getFormId()
  {
    return 'add_family_member_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#prefix'] = '<div id="add-family-member-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['#attributes']['class'][] = 'form-sec border-inherit border-2 rounded-xl p-4 lg:px-10 lg:py-12 bg-white text-center lg:text-start s:mb-24 xs:mb-20';

    $form['user_id'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'user_id'],
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'px-2.5',
          'pb-2.5',
          'pt-4',
          'text-sm',
          'text-medium_dark',
          'bg-transparent',
          'rounded-lg',
          'border',
          'border-gray-300',
          'appearance-none',
          'text-base',
          's:text-sm',
          'xs:text-sm'
        ],
        'placeholder' => ' ',
        'autocomplete' => 'off',
        'id' => 'first_name',
      ],
      '#prefix' => '<div class="errors-first_name"><div class="relative">',
      '#suffix' => '</div></div>',
    ];

    $form['calendar'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of Birth'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'px-2.5',
          'pb-2.5',
          'pt-4',
          'text-sm',
          'text-medium_dark',
          'bg-transparent',
          'rounded-lg',
          'border',
          'border-gray-300',
          'text-base',
          's:text-sm',
          'xs:text-sm'
        ],
        'placeholder' => 'DD-MM-YYYY',
        'id' => 'calendar',
        'max' => date('Y-m-d'),
      ],
      '#prefix' => '<div class="errors-calendar"><div class="relative">',
      '#suffix' => $this->getDateErrorMarkup($form_state, 'calendar') . '</div></div>',
    ];


    $form['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender'),
      '#required' => TRUE,
      '#options' => [
        // '' => $this->t('Gender'),
        1 => $this->t('Male'),
        2 => $this->t('Female'),
        3 => $this->t('Others'),
      ],
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'text-base',
          's:text-sm',
          'xs:text-sm',
          'font-medium',
          'select',
          'rounded-lg',
          'border',
          'border-gray-300',
          'px-2.5',
          'pb-2.5',
          'pt-4'
        ],
        'id' => 'gender',
      ],
      '#prefix' => '<div class="errors-gender"><div class="relative">',
      '#suffix' => '</div></div>',
    ];

    $form['relations'] = [
      '#type' => 'select',
      '#title' => $this->t('Relationship'),
      '#required' => TRUE,
      '#options' => [
        '' => $this->t('Relationship*'),
        1 => $this->t('Mother'),
        2 => $this->t('Father'),
        3 => $this->t('Sister'),
        4 => $this->t('Brother'),
        5 => $this->t('Wife'),
        6 => $this->t('Husband'),
        7 => $this->t('Daughter'),
        8 => $this->t('Son'),
      ],
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'text-base',
          's:text-sm',
          'xs:text-sm',
          'font-medium',
          'select',
          'rounded-lg',
          'border',
          'border-gray-300',
          'px-2.5',
          'pb-2.5',
          'pt-4'
        ],
        'id' => 'relations',
      ],
      '#prefix' => '<div class="errors-relations"><div class="relative">',
      '#suffix' => '</div></div>',
    ];

    $form['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'text-base',
          's:text-sm',
          'xs:text-sm',
          'rounded-lg',
          'border',
          'border-gray-300',
          'px-2.5',
          'pb-2.5',
          'pt-4'
        ],
        'maxlength' => 10,
        'minlength' => 10,
        'id' => 'phone_number',
        // 'onkeypress' => 'return validateNumber(event)',
        'placeholder' => ' ',
      ],
      '#prefix' => '<div class="errors-phone_number"><div class="relative">',
      '#suffix' => '</div></div>',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email ID'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'text-base',
          's:text-sm',
          'xs:text-sm',
          'rounded-lg',
          'border',
          'border-gray-300',
          'px-2.5',
          'pb-2.5',
          'pt-4'
        ],
        'placeholder' => ' ',
        'id' => 'email',
      ],
      '#prefix' => '<div class="errors-email"><div class="relative">',
      '#suffix' => '</div></div>',
    ];

    $form['upload_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload Picture'),
      '#description' => $this->t('(Supported file types: JPG, JPEG & PNG, 2MB max)'),
      // '#upload_validators' => [
      //   'file_validate_extensions' => ['jpg', 'jpeg', 'png'],
      //   'file_validate_size' => [2 * 1024 * 1024],  // 2MB max
      // ],
      '#attributes' => [
        'class' => [
          'peer',
          'w-full',
          'lg:max-w-lg',
          'px-2.5',
          'pb-2.5',
          'pt-4',
          'text-sm',
          'text-medium_dark',
          'bg-transparent',
          'rounded-lg',
          'text-base',
          's:text-sm',
          'xs:text-sm'
        ]
      ],
      '#prefix' => '<div class="file-upload-wrapper file-upload-container no-float-label">',
      '#suffix' => '</div>',
    ];
    


    $form['terms'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I agree to the Terms and Conditions'),
      '#required' => TRUE,
      // '#title_display' => 'invisible', // hides label wrapper
      '#attributes' => [
        'class' => ['checkbox', 'just-validate-success-field'],
        'id' => 'terms',
      ],
      // Wrap only the checkbox visually
      '#prefix' => '<div class="terms-container flex items-center space-x-2 no-float-label">',
      '#suffix' => '</div><div class="no-type-checkbox">' . $this->getDateErrorMarkup($form_state, 'terms') . '</div>',
    ];



    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => [
          'btn',
          'btn-warning',
          'lg:h-14',
          'lg:w-44',
          'xs:h-10',
          'text-white',
          'capitalize',
          'text-lg',
          'submitBtn',
          'engage-btn'
        ],
      ],
      // '#ajax' => [
      //   'callback' => '::ajaxCallback',
      //   'wrapper' => 'add-family-member-form-wrapper',
      //   'effect' => 'fade',
      // ],
    ];
    $form['actions']['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'onclick' => 'window.location.reload()',
        'class' => [
          'btn',
          'btn-outline',
          'lg:h-14',
          'lg:w-44',
          'xs:h-10',
          'capitalize',
          'text-medium_dark',
          'text-lg',
          "engage-cancel-btn"
        ],
      ],
    ];
    $form['#theme'] = 'add-family-member';
    $form['#attached']['library'][] = 'profile/add-family-member-library';
    return $form;
  }

public function submitForm(array &$form, FormStateInterface $form_state) {
  
  $file_ids = $form_state->getValue('upload_file');
  
  // Single file (if only one upload is allowed)
  if (!empty($file_ids[0])) {
    $file = \Drupal\file\Entity\File::load($file_ids[0]);

    if ($file) {
      // Make sure file is permanent (optional, otherwise it might get deleted later)
      $file->setPermanent();
      $file->save();

      // Get detailed file info
      $file_info = [
        'fid' => $file->id(),
        'filename' => $file->getFilename(),
        'uri' => $file->getFileUri(),
        'realpath' => \Drupal::service('file_system')->realpath($file->getFileUri()),
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri()),
        'filesize (bytes)' => $file->getSize(),
        'mime' => $file->getMimeType(),
      ];

      // Dump it
      //  $upload_response = \Drupal::service('global_module.global_variables')->fileUploadser($file_info);
      // dump($upload_response);exit; // OR use logger
      \Drupal::logger('profile')->info('<pre>@info</pre>', ['@info' => print_r($file_info, true)]);
    }
  };
  // $form_state->set('upload_file', $file_data);
  // dump($file_ids);exit;
  // if (isset($_FILES['files']['full_path']['upload_file']) && is_uploaded_file($_FILES['files']['tmp_name']['upload_file'])) {
  //     // $upload_response = \Drupal::service('global_module.global_variables')->fileUploadser($file_info);
  //     if ($upload_response instanceof JsonResponse) {
  //       $response_data = json_decode($upload_response->getContent(), true);
  //       if (!empty($response_data['fileName'])) {
  //         $image_url = $response_data['fileName'];
  //       } elseif (!empty($response_data['error'])) {
  //         $this->messenger()->addError($this->t('File upload error: @error', ['@error' => $response_data['error']]));
  //         return;
  //       }
  //     }
  //   }
  $values = $form_state->getValues();
  $session = \Drupal::request()->getSession();
  $user_data = $session->get('api_redirect_result') ?? [];

  $access_token = \Drupal::service('global_module.global_variables')->getApimanAccessToken();
  $globalVariables = \Drupal::service('global_module.global_variables')->getGlobalVariables();

  $payload = [
    'name' => $values['first_name'],
    'dateOfBirth' => $values['calendar'],
    'gender' => $values['gender'],
    'relationship' => $values['relations'],
    'phone' => $values['phone_number'],
    'emailId' => $values['email'],
    'userId' => $user_data['userId'] ?? '',
    'imageUrl' => 'dewuiwhfh',
  ];

  \Drupal::logger('profile')->info('Submitting family member: <pre>@payload</pre>', [
    '@payload' => print_r($payload, TRUE),
  ]);

  try {
    $api_response = $this->httpClient->post(
      $globalVariables['apiManConfig']['config']['apiUrl'] .
      'tiotcitizenapp' .
      $globalVariables['apiManConfig']['config']['apiVersion'] .
      'family-members/add-family-member',
      [
        'json' => $payload,
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ]
    );

    $data = json_decode($api_response->getBody()->getContents(), true);

    \Drupal::logger('profile')->info('API response: <pre>@response</pre>', [
      '@response' => print_r($data, TRUE),
    ]);

    if (!empty($data['status']) && $data['status'] === true) {
      // ✅ Clear form values manually
      $form_state->setValues([]);
      $form_state->setRebuild();

      // Redirect to success page
      $form_state->setResponse(
        (new AjaxResponse())->addCommand(new RedirectCommand('/success-family-member'))
      );
      return;
    }

    // Redirect to failure page
    $form_state->setResponse(
      (new AjaxResponse())->addCommand(new RedirectCommand('/failed-family-member'))
    );
  }
  catch (\Exception $e) {
    \Drupal::logger('profile')->error('API error: @message', [
      '@message' => $e->getMessage(),
    ]);

    $form_state->setResponse(
      (new AjaxResponse())->addCommand(new RedirectCommand('/failed-family-member'))
    );
  }
}


  private function getDateErrorMarkup(FormStateInterface $form_state, $field_name)
  {
    $errors = $form_state->getErrors();
    if (isset($errors[$field_name])) {
      return '<p class="text-red-600 text-sm mt-1">' . $errors[$field_name] . '</p>';
    }
    return '';
  }
  public function ajaxCallback(array &$form, FormStateInterface $form_state)
  {
    if ($form_state->hasAnyErrors()) {
    // Return the entire form to show errors, but do NOT process submit
    return $form;
  }
   return $form;
  }

}
