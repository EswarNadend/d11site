<?php

namespace Drupal\global_module\Service;

use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
class GlobalVariablesService
{

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;
   /**
     * HTTP client for making external requests.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    public const STR_STS = 'status';
    const PAYLOADS = 'payload';
    protected $httpClient;
    protected $apiUrl;
    protected $apiVer;
    protected $webportalUrl;

  /**
   * Constructs a new GlobalVariablesService.
   * @param \GuzzleHttp\ClientInterface $http_client
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory)
  {
    $this->logger = $logger_factory->get('custom_services');
     $this->httpClient = $http_client;
        $this->logger = $logger_factory->get('global_module'); // ✅ Your module name
        $globalVariables = $this->getGlobalVariables();
        $this->apiUrl = $globalVariables['apiManConfig']['config']['apiUrl'];
        $this->apiVer = $globalVariables['apiManConfig']['config']['apiVersion'];
        $this->webportalUrl = $globalVariables['applicationConfig']['config']['webportalUrl'];
  }

  /**
   * Fetches and returns global variables from Vault.
   *
   * @return array|null
   */
  public function getGlobalVariables(): ?array
  {
    try {
      $vaultAPI = Settings::get('vault_url');
      $vaultToken = Settings::get('vault_token');

      $response = \Drupal::httpClient()->get($vaultAPI, [
        'headers' => [
          'Content-Type' => 'application/json',
          'X-Vault-Token' => $vaultToken,
        ],
      ]);

      $data = $response->getBody()->getContents();

      if ($data) {
        $vaultData = json_decode($data)->data ?? [];
        $vaultData = json_decode(json_encode($vaultData), true);

        $vaultData = array_merge(
          ['webportalUrl' => $vaultData['applicationConfig']['config']['webportalUrl'] ?? ''],
          $vaultData
        );
        $vaultData = array_merge(
          ['siteUrl' => $vaultData['applicationConfig']['config']['siteUrl'] ?? ''],
          $vaultData
        );

        return $vaultData;
      }
    } catch (GuzzleException $e) {
      $this->logger->error('Vault fetch failed: ' . $e->getMessage());
      \Drupal::messenger()->addError('Our service is currently down, please try again later.');
    }

    return NULL;
  }

  public function getApiUrl(): ?string
  {
    $globals = $this->getGlobalVariables();
    return $globals['apiManConfig']['config']['apiUrl'] ?? NULL;
  }

  public function getApiVersion(): ?string
  {
    $globals = $this->getGlobalVariables();
    return $globals['apiManConfig']['config']['apiVersion'] ?? NULL;
  }


  public function getApimanAccessToken(): ?string
  {
    $globals = $this->getGlobalVariables();

    if (empty($globals['apiManConfig']['config'])) {
      $this->logger->error('Missing apiManConfig configuration in Vault response.');
      return NULL;
    }

    $tokenUrl = $globals['apiManConfig']['config']['apiUrl']
      . 'tiotAPIESBSubSystem'
      . $globals['apiManConfig']['config']['apiVersion']
      . 'getAccessToken';

    try {
      $client = \Drupal::httpClient();
      $response = $client->post($tokenUrl, [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => json_encode($globals['apiManConfig']['config']),
        'verify' => FALSE,
      ]);

      $data = json_decode($response->getBody(), TRUE);
      return $data['access_token'] ?? NULL;
    } catch (RequestException $e) {
      $this->logger->error('Apiman token fetch failed: ' . $e->getMessage());
      return NULL;
    }
  }

      public function getServiceUrl($serviceName)
    {
        // if ($serviceName === null || $serviceName === '') {
        //     return new JsonResponse(array('status' => false, 'message' => 'Service not available'));
        // }

        $serUrl = '';

        if ($serviceName == 'cep') {
            $serUrl = $this->apiUrl . 'trinityengage-casemanagementsystem' . $this->apiVer;
        } elseif ($serviceName == 'cad') {
            $serUrl = $this->apiUrl . 'trinity-respond' . $this->apiVer;
        } elseif ($serviceName == 'ngcad') {
            $serUrl = $this->apiUrl . 'ngcadmobileapp' . $this->apiVer;
        } elseif ($serviceName == 'iot') {
            $serUrl = $this->apiUrl . 'tiotIOTPS' . $this->apiVer;
        } elseif ($serviceName == 'cityapp') {
            $serUrl = $this->apiUrl . 'tengageCity' . $this->apiVer;
        } elseif ($serviceName == 'idam') {
            $serUrl = $this->apiUrl . 'UMA' . $this->apiVer;
        } elseif ($serviceName == 'tiotweb') {
            $serUrl = $this->apiUrl . 'tiotweb' . $this->apiVer;
        } elseif ($serviceName == 'tiotICCCOperator') {
            $serUrl = $this->apiUrl . 'tiotICCCOperator' . $this->apiVer;
        } elseif ($serviceName == 'tiotcitizenapp') {
            $serUrl = $this->apiUrl . 'tiotcitizenapp' . $this->apiVer;
        } elseif ($serviceName == 'innv') {
            $serUrl = $this->apiUrl . 'tiotcitizenapp' . $this->apiVer;
        } elseif ($serviceName == 'portal') {
            $serUrl = $this->webportalUrl;
        } else {
            $serUrl = '';
        }

        return $serUrl;
    }

    private function buildServiceUrl(array $data): ?string
    {
        $base = $this->getServiceUrl($data['service'] ?? '');
        return $base ? $base . ($data['endPoint'] ?? '') : null;
    }

    public function postData(Request $request): JsonResponse
    {
        try {
            $method = $request->getMethod();
            if ($method !== 'POST') {
                return new JsonResponse(['status' => false, 'message' => 'Method not allowed!']);
            }

            $postData = json_decode($request->getContent(), true);
            if (!$postData || !isset($postData['service'], $postData['type'])) {
                return new JsonResponse(['status' => false, 'message' => 'Invalid payload!']);
            }

            $user = \Drupal::currentUser();
            $userId = $user->id();
            // $this->validatePostData->urlForm($postData);
            $url = $this->buildServiceUrl($postData);
            $response = $this->handleRequestByType($postData, $url, $request, $userId);

            return new JsonResponse($response);

        } catch (\Exception $e) {
            \Drupal::logger('Post Data Error')->error($e->getMessage());
            return new JsonResponse(['status' => false, 'message' => 'Internal server error.'], 500);
        }
    }

    public function handleRequestByType(array $data, string $url, Request $request, int $userId): array
    {
         $session = \Drupal::request()->getSession();
        $user_data = $session->get('api_redirect_result') ?? [];
        $type = $data['type'] ?? null;
        $payload = $data[self::PAYLOADS] ?? [];

        switch ($type) {
            case 2:
                return $this->curl_post_apiman($url, $payload); // Assuming it's a method in this class
            case 'delyUser':
                return $this->userDelete(
                    userID: $user_data['userId'],
                    tenantCode: $user_data['tenantCode']
                );

            default:
                return $this->curl_post_apiman($url, $payload); // Fallback/default handler
        }
    }


    public function decrypt($value)
    {
        // dump("ess",$value);
        $key = "Fl%JTt%d954n@PoU";
        $cipher = "AES-128-ECB";

        try {

            $ciphertext = base64_decode($value);

            $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

            if ($decrypted !== false) {

                $pad = ord($decrypted[strlen($decrypted) - 1]);
                $decrypted = substr($decrypted, 0, -$pad);
            }

            return $decrypted;
        } catch (\Exception $e) {
            error_log('Decryption error: ' . $e->getMessage());
            return null;
        }
    }




    public function fileUploadser(Request $request)
    {
        define('UPLOAD_FILE', 'uploadedfile1');
        $globalVariables = $this->getGlobalVariables();

        if (!isset($_FILES[UPLOAD_FILE])) {
            return new JsonResponse(['status' => false, 'message' => 'No file uploaded.']);
        }

        $originalName = $_FILES[UPLOAD_FILE]['name'];
        $tmpName = $_FILES[UPLOAD_FILE]['tmp_name'];
        $mimeType = mime_content_type($tmpName);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extnParts = explode(".", $originalName);

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'video/mp4'
        ];

        if (!in_array($mimeType, $allowedTypes)) {
            return new JsonResponse(['status' => false, 'message' => 'File content not allowed!']);
        }

        if (count($extnParts) > 2) {
            return new JsonResponse(['message' => 'Multiple file extensions not allowed', self::STR_STS => false]);
        }

        // Detect file type   
        $fileTypeVal = null;
        $fileTypeType = null;
        $extensionLower = strtolower($extension);
        if (in_array($extensionLower, ['jpg', 'jpeg', 'png'])) {
            $fileTypeVal = 2;
            $fileTypeType = "image";
        } elseif (in_array($extensionLower, ['pdf', 'doc', 'docx', 'mp3', 'xlsx'])) {
            $fileTypeVal = 4;
            $fileTypeType = "file";
        } elseif ($extensionLower === 'mp4') {
            $fileTypeVal = 1;
            $fileTypeType = "video";
        }

        if (!$fileTypeVal) {
            return new JsonResponse(['message' => 'Unsupported file type.', self::STR_STS => false]);
        }

        $uuidFilename = \Drupal::service('uuid')->generate() . '.' . $extension;
        $fileUplPath = $globalVariables['applicationConfig']['config']['fileuploadPath'];

        try {
            // Upload using HTTP client with multipart
            $response = $this->httpClient->request('POST', $fileUplPath . 'upload_media_test1.php', [
                'verify' => false,
                'multipart' => [
                    [
                        'name' => UPLOAD_FILE,
                        'contents' => fopen($tmpName, 'r'),
                        'filename' => $uuidFilename,
                        'headers' => [
                            'Content-Type' => $mimeType
                        ]
                    ],
                    [
                        'name' => 'success_action_status',
                        'contents' => '200',
                    ]
                ]
            ]);

            $responseBody = $response->getBody()->getContents();
            $this->logger->debug('Upload raw response: ' . $responseBody);
            $decoded = json_decode($responseBody, true);

            // Optional profilePic update
            if ($request->request->get('userPic') === 'profilePic') {
                $profilePic = $fileUplPath . $uuidFilename;
                return $this->updateUserProfilePic($profilePic);
            }

            return new JsonResponse([
                'fileName' => $fileUplPath . $uuidFilename,
                'fileTypeId' => $fileTypeVal,
                'fileTypeVal' => $fileTypeType,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('File upload failed: @error', ['@error' => $e->getMessage()]);
            return new JsonResponse(['status' => false, 'message' => 'Upload error'], 500);
        }
    }


    public function updateUserProfilePic($profilePic)
    {
        try {
            $globalVariables = $this->getGlobalVariables();
            $session = \Drupal::request()->getSession();
            $user_data = $session->get('api_redirect_result') ?? [];
            $access_token = $this->getApimanAccessToken();
            if (empty($user_data['mobileNumber'])) {
                return new JsonResponse(['status' => false, 'message' => 'Mobile number not found in session'], 400);
            }

            // $apiEngage = $globalVariables['applicationConfig']['config']['apimantEngage'];
            $payload = [
                'mobileNumber' => $user_data['mobileNumber'],
                'profilePic' => $profilePic,
                'firstName' => $user_data['firstName'],
                'lastName' => $user_data['lastName'],
                'emailId' => $user_data['emailId'],
                'tenantCode' => $user_data['tenantCode'],
                'userId' => $user_data['userId']
            ];
            $url = $globalVariables['apiManConfig']['config']['apiUrl'] . 'tiotcitizenapp' . $globalVariables['apiManConfig']['config']['apiVersion'] . 'user/update';
            $this->logger->debug('Profile update URL: @url', ['@url' => $url]);

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token,
                ],
                'json' => $payload,
                'timeout' => 10,
                'verify' => false, // optional: disable SSL verification for dev
            ]);
            // dump($response);
            $result = json_decode($response->getBody()->getContents(), true);
            // dump($result);
            $session->remove('api_redirect_result');
            $this->logger->debug('Profile update result:', [
                'Message' => json_encode($result['data']),
            ]);
            return new JsonResponse([
                'status' => true,
                'profilePic' => $result['data']['profilePic'] ?? null,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Profile update failed: @msg', ['@msg' => $e->getMessage()]);
            return new JsonResponse([
                'status' => false,
                'message' => 'Profile update failed',
            ], 500);
        }
    }







   public function curl_post_apiman($url, $payload, $method = 'POST') {
  try {
    $client = \Drupal::httpClient();
    $method = strtoupper($method); // Ensure method is uppercase (POST/PATCH)

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $this->getApimanAccessToken(),
      ],
      'json' => $payload,
    ];

    // Call dynamically based on method (POST by default)
    switch ($method) {
      case 'PATCH':
        $response = $client->patch($url, $options);
        break;

      case 'PUT':
        $response = $client->put($url, $options);
        break;

      default: // Default to POST
        $response = $client->post($url, $options);
    }

    $body = $response->getBody()->getContents();
    return json_decode($body, true);
  } catch (RequestException $e) {
    \Drupal::logger('global_module')->error('HTTP request failed: @message', ['@message' => $e->getMessage()]);
    return ['error' => 'Request failed'];
  }
}


   public function curl_post_idam($url, $payload, $method = 'POST') {
  try {
    $client = \Drupal::httpClient();
    $method = strtoupper($method); // Ensure method is uppercase (POST/PATCH)
     $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'form_params' => $payload,
      'verify' => false,
    ];

    // Call dynamically based on method (POST by default)
    switch ($method) {
      case 'PATCH':
        $response = $client->patch($url, $options);
        break;

      case 'PUT':
        $response = $client->put($url, $options);
        break;

      default: // Default to POST
        $response = $client->post($url, $options);
    }

    $body = $response->getBody()->getContents();
    return json_decode($body, true);
  } catch (RequestException $e) {
    \Drupal::logger('global_module')->error('HTTP request failed: @message', ['@message' => $e->getMessage()]);
    return ['error' => 'Request failed'];
  }
}
public function curl_post_idam_auth($url, $payload, $method = 'POST') {
  try {
    $client = \Drupal::httpClient();
    $method = strtoupper($method); // Ensure method is uppercase (POST/PATCH)
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode('trinity:trinity@123'),
      ],
      'json' => $payload,
      'verify' => false,
    ];

    // Call dynamically based on method (POST by default)
    switch ($method) {
      case 'PATCH':
        $response = $client->patch($url, $options);
        break;

      case 'PUT':
        $response = $client->put($url, $options);
        break;

      default: // Default to POST
        $response = $client->post($url, $options);
    }

    $body = $response->getBody()->getContents();
    return json_decode($body, true);
  } catch (RequestException $e) {
    \Drupal::logger('global_module')->error('HTTP request failed: @message', ['@message' => $e->getMessage()]);
    return ['error' => 'Request failed'];
  }
}


    public function curlDeleteApiman(string $url): array
    {
        try {
            $client = \Drupal::httpClient();

            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    // Add Authorization if needed:
                    'Authorization' => 'Bearer ' . $this->getApimanAccessToken(),
                ],
                'timeout' => 10,
            ]);

            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (\Exception $e) {
            \Drupal::logger('apiman')->error('Delete request failed: @message', ['@message' => $e->getMessage()]);
            return ['status' => false, 'error' => 'Request failed'];
        }
    }


/**
 * POST request using Drupal's HTTP client.
 */
public function curl_post_api($url) {
  try {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = \Drupal::httpClient(); // Or inject ClientInterface in constructor

    $response = $client->request('POST', $url, [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'verify' => false, // Disable SSL verification (not for production)
    ]);

    $contents = $response->getBody()->getContents();
    return json_decode($contents, TRUE);
  }
  catch (\Exception $e) {
    \Drupal::logger('custom_module')->error('HTTP POST failed: @error', [
      '@error' => $e->getMessage(),
    ]);
    return NULL;
  }
}

public function curl_get_api($url) {
  try {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = \Drupal::httpClient(); // Alternatively inject via constructor

    $response = $client->request('GET', $url, [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode('trinity:trinity@123'),
        // 'Authorization' => 'Basic ' . base64_encode('admin:admin'),
      ],
      'verify' => false, // Disable SSL verification (only for dev/test)
    ]);

    $contents = $response->getBody()->getContents();
    return json_decode($contents, TRUE);
  }
  catch (\Exception $e) {
    \Drupal::logger('custom_module')->error('HTTP GET failed: @error', [
      '@error' => $e->getMessage(),
    ]);
    return NULL;
  }
}



    public function userDelete($userID, $tenantCode)
    {
        $globalVariables = $this->getGlobalVariables();
        // $idamClientId = $globalVariables['applicationConfig']['config']['idamClientId'];
        $user = User::load(\Drupal::currentUser()->id());
        $email = $user->get('mail')->value;
        // echo $email;
        $payload = '{
        "schemas": [
            "urn:ietf:params:scim:api:messages:2.0:SearchRequest"
        ],

        "filter": "userName eq ' . $email . '",
        "domain":"PRIMARY"
        }';

        $cityUrl = $globalVariables['applicationConfig']['config']['deleteAPICA'] . $userID;
        \Drupal::logger('City App Delete Url')->notice($cityUrl);
        $deleteResponse = $this->curl_post_api($cityUrl);
        \Drupal::logger('Post Data response')->notice(print_r($deleteResponse, true));
        if (($deleteResponse && isset($deleteResponse['status']) && $deleteResponse['status'] === true)) {

            $responseData = $this->curl_post_apiman(
                $this->apiUrl . 'trinityengage-casemanagementsystem' . $this->apiVer . 'user/delete-user?userId=' . $userID . '&tenantCode=' . $tenantCode,
                ''
            );


            \Drupal::logger('CEP Delete API response')->notice(print_r($responseData, true));
            if ($responseData['status'] == true) {
                $account = User::load(\Drupal::currentUser()->id());
                $account->delete();
                return ['status' => true, 'message' => 'User account deleted successfully!'];
            } else {
                return ['status' => false, 'message' => 'Failed to delete user from case management system.'];
            }
        } else {
            return ['status' => false, 'message' => 'Failed to delete user account.'];
        }

    }

}
