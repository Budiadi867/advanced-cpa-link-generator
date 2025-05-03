<?php
header('Content-Type: application/json');

define('SITE_URL', 'https://chatdatlng.biz.id/n');
define('TOKEN_LENGTH', 12);

class LinkGenerator {
    private $smartlink;
    private $campaign;
    private $features;
    private $ogData;

    private $defaultTitles = [
        "Don't Miss This Offer!",
        "Limited Time Deal!",
        "Exclusive Promotion!",
        "Best Offer Today!",
        "Special Discount Inside!",
        "New Exciting Deal Just For You!",
        "Hurry! Offer Ends Soon!"
    ];

    private $defaultDescriptions = [
        "Grab this amazing deal before it's gone.",
        "Limited availability, act fast!",
        "Exclusive offer just for you.",
        "Save big with this special promotion.",
        "Don't wait, get it now!",
        "Unlock your special discount today.",
        "Join thousands who are saving big!"
    ];

    public function __construct($data) {
        $this->smartlink = 'https://fbhhhg.naughtymets.com/s/5f54849de4bb0?subsource=hhhh&track=hhhh&ext_click_id=jjjjj'; // Fixed Trafee Smartlink URL
        $this->campaign = 'Permanent Campaign Name'; // Set permanent campaign name here
        $this->features = $data['features'] ?? [];

        $title = trim($data['og_title']);
        $description = trim($data['og_description']);

        if (empty($title)) {
            $title = $this->defaultTitles[array_rand($this->defaultTitles)];
        }
        if (empty($description)) {
            $description = $this->defaultDescriptions[array_rand($this->defaultDescriptions)];
        }

        $this->ogData = [
            'title' => $title,
            'description' => $description,
            'image' => filter_var($data['og_image'], FILTER_VALIDATE_URL)
        ];
    }

    public function generate() {
        if (!$this->validateInputs()) {
            return $this->error('Please fill all required fields with valid data');
        }

        try {
            $token = $this->generateToken();

            $proxyImageUrl = '';
            if (!empty($this->ogData['image'])) {
                $imageFileName = basename(parse_url($this->ogData['image'], PHP_URL_PATH));
                $proxyImageUrl = SITE_URL . '/image_proxy.php?img=' . urlencode($imageFileName);
            }
            $this->ogData['image'] = $proxyImageUrl;

            $this->storeLink($token);

            $protectedUrl = $this->createProtectedUrl($token);

            return [
                'success' => true,
                'url' => $protectedUrl,
                'preview' => [
                    'title' => $this->ogData['title'],
                    'description' => $this->ogData['description'],
                    'image' => $this->ogData['image']
                ],
                'features' => [
                    'mobile' => in_array('mobile', $this->features),
                    'facebook' => in_array('fb', $this->features),
                    'fingerprint' => in_array('fingerprint', $this->features)
                ]
            ];
        } catch (Exception $e) {
            return $this->error('Error generating link: ' . $e->getMessage());
        }
    }

    private function validateInputs() {
        return $this->smartlink &&
               !empty($this->campaign) &&
               !empty($this->ogData['title']) &&
               !empty($this->ogData['description']) &&
               $this->ogData['image'];
    }

    private function generateToken() {
        return bin2hex(random_bytes(TOKEN_LENGTH));
    }

    private function storeLink($token) {
        $data = [
            'token' => $token,
            'smartlink' => $this->smartlink,
            'campaign' => $this->campaign,
            'features' => $this->features,
            'og' => $this->ogData,
            'created' => time()
        ];

        $links = [];
        if (file_exists('links.json')) {
            $links = json_decode(file_get_contents('links.json'), true) ?? [];
        }

        $links[] = $data;

        if (!file_put_contents('links.json', json_encode($links, JSON_PRETTY_PRINT))) {
            throw new Exception('Could not store link data');
        }
    }

    private function createProtectedUrl($token) {
        return SITE_URL . '/redirect.php?token=' . urlencode($token);
    }

    private function error($message) {
        return [
            'success' => false,
            'error' => $message
        ];
    }
}

try {
    $generator = new LinkGenerator($_POST);
    echo json_encode($generator->generate());
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'System error: ' . $e->getMessage()
    ]);
}
?>
