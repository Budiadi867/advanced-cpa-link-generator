<?php
class AdvancedMobileProtection {
    private $mobileProfiles = [
        'android_chrome' => [
            'devices' => [
                'Samsung S23' => 'Mozilla/5.0 (Linux; Android 13; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
                'Pixel 7' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
                'OnePlus 11' => 'Mozilla/5.0 (Linux; Android 13; OnePlus 11) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
                'Xiaomi 13' => 'Mozilla/5.0 (Linux; Android 13; M2102J20SG) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36'
            ],
            'screens' => ['1440x3200', '1080x2400', '1080x2340'],
            'fbApp' => 'Mozilla/5.0 (Linux; Android {ver}; {device}) AppleWebKit/537.36 (KHTML, like Gecko) FB_IAB/FB4A;FBAV/{fb_ver} Mobile Safari/537.36'
        ],
        'ios_safari' => [
            'devices' => [
                'iPhone 14 Pro' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
                'iPhone 13' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
                'iPad Pro' => 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
            ],
            'screens' => ['1290x2796', '1170x2532', '1024x1366'],
            'fbApp' => 'Mozilla/5.0 (iPhone; CPU iPhone OS {ver} like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FB_IAB/{fb_ver} Mobile/15E148 Safari/604.1'
        ]
    ];

    private $fbAppVersions = [
        ['version' => '510.0.0.72.41', 'build' => 'UP1A.231005.007'],
        ['version' => '511.0.0.44.52', 'build' => 'UP1A.231005.012'],
        ['version' => '512.0.0.38.26', 'build' => 'UP1A.231005.015'],
        ['version' => '513.0.0.41.38', 'build' => 'TQ1A.230105.019'],
        ['version' => '509.0.0.68.35', 'build' => 'SP1A.231005.003'],
        ['version' => '508.0.0.55.47', 'build' => 'SP1A.230905.011']
    ];

    private $androidVersions = ['12', '13', '14'];
    
    private $deviceModels = [
        'SM-M146B' => 'Samsung Galaxy M14',
        'SM-A546B' => 'Samsung Galaxy A54',
        'SM-S918B' => 'Samsung Galaxy S23+',
        'CPH2471' => 'OPPO Reno8',
        'V2171A' => 'Vivo V25',
        '2201117TY' => 'Xiaomi 12'
    ];

    private $carriers = [
        'Telkomsel', 'XL', 'Indosat', 'Smartfren', '3'
    ];

    private $fbSources = [
        'feed' => ['type' => 'feed', 'weight' => 35],
        'group' => ['type' => 'group', 'weight' => 25],
        'story' => ['type' => 'story', 'weight' => 20],
        'reels' => ['type' => 'reels', 'weight' => 15],
        'other' => ['type' => 'other', 'weight' => 5]
    ];

    public function process($smartlink) {
        $identity = $this->generateMobileIdentity();
        $identity['subsource'] = 'Barat4nn';
        $identity['track'] = 'Barat4nn';
        $this->clearMobileTraces();
        $this->setMobileHeaders($identity);
        $this->simulateMobileStorage($identity);
        $finalUrl = $this->addTrackingParameters($smartlink, $identity);
        $this->addRandomDelay();
        return $finalUrl;
    }

    private function generateMobileIdentity() {
        $fbApp = $this->fbAppVersions[array_rand($this->fbAppVersions)];
        $deviceModel = array_rand($this->deviceModels);
        $androidVer = $this->androidVersions[array_rand($this->androidVersions)];
        $userAgent = sprintf(
            'Mozilla/5.0 (Linux; Android %s; %s Build/%s; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/135.0.%d.%d Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/%s;FBBV/%d;FBDM/{density=2.0,width=720,height=1600};]',
            $androidVer,
            $deviceModel,
            $fbApp['build'],
            mt_rand(7000, 7500),
            mt_rand(100, 200),
            $fbApp['version'],
            mt_rand(400000000, 499999999)
        );
        return [
            'user_agent' => $userAgent,
            'device' => $deviceModel,
            'android_ver' => $androidVer,
            'fb_version' => $fbApp['version'],
            'build' => $fbApp['build'],
            'carrier' => $this->carriers[array_rand($this->carriers)],
            'is_fb_app' => true
        ];
    }

    private function clearMobileTraces() {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-3600, '/');
                setcookie($name, '', time()-3600, '/', '', true, true);
            }
        }
        header('Clear-Site-Data: "cache","cookies","storage","executionContexts"');
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }
        session_id(md5(uniqid() . random_bytes(8)));
        session_start();
        setcookie('_fb_' . bin2hex(random_bytes(8)), '1', time()+3600, '/');
    }

    private function setMobileHeaders($identity) {
        header('User-Agent: ' . $identity['user_agent']);
        header('X-Requested-With: com.facebook.katana');
        header('X-FB-Connection-Type: MOBILE.LTE');
        header('X-FB-Net-HNI: ' . mt_rand(50001, 50020));
        header('X-FB-Connection-Quality: EXCELLENT');
        header('X-FB-Friendly-Name: feed_timeline');
        header('X-FB-Request-Analytics-Tags: unknown');
        header('X-FB-HTTP-Engine: Liger');
        header('X-FB-Client-IP: ' . $_SERVER['REMOTE_ADDR']);
        header('X-Carrier: ' . $identity['carrier']);
        header('x-fb-device: Android ' . $identity['android_ver']);
        header('x-fb-session-id: ' . bin2hex(random_bytes(16)));
        header('Accept-Language: ' . $this->getRandomLanguage());
        header('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        header('Accept-Encoding: gzip, deflate');
        header('Connection: keep-alive');
        header('DNT: ' . mt_rand(0, 1));
    }

    private function simulateMobileStorage($identity) {
        if ($identity['is_fb_app']) {
            $_SESSION['_fb_device_id'] = bin2hex(random_bytes(16));
            $_SESSION['_device_id'] = bin2hex(random_bytes(16));
            $_SESSION['_app_version'] = $identity['fb_version'];
            $_SESSION['_install_source'] = 'PlayStore';
        }
    }

    private function addTrackingParameters($url, $identity) {
        $timestamp = time();
        $params = [
            'fb_source' => $this->getRandomSource(),
            'fb_ref' => $this->generateFBRef(),
            'fbclid' => $this->generateEnhancedFBClid($timestamp),
            'device_id' => md5($identity['device'] . $timestamp),
            'platform' => 'Android',
            'carrier' => urlencode($identity['carrier']),
            'network' => ['4G', '5G'][array_rand([0,1])],
            'app_version' => $identity['fb_version'],
            'click_time' => $timestamp,
            'unique' => bin2hex(random_bytes(8)),
            'session' => md5(uniqid() . random_bytes(4)),
            'subsource' => 'Barat4nn',
            'track' => 'Barat4nn',
            'ext_click_id' => $this->generateClickID(),
            '_rnd' => mt_rand(1000000, 9999999),
            '_t' => $timestamp,
            '_sig' => md5($timestamp . random_bytes(8))
        ];
        return $this->appendParams($url, $params);
    }

    private function generateSourceSignature($identity) {
        $sources = ['organic', 'social', 'direct'];
        return $sources[array_rand($sources)] . '_' . substr(md5(uniqid()), 0, 8);
    }

    private function generateTrackingID($identity) {
        return implode('_', [
            'android',
            time(),
            substr(md5(random_bytes(6)), 0, 6)
        ]);
    }

    private function generateClickID() {
        return bin2hex(random_bytes(8));
    }

    private function generateEnhancedFBClid($timestamp) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $fbclid = 'IwAR';
        $fbclid .= base64_encode(pack('N', $timestamp));
        $fbclid .= base64_encode(random_bytes(4));
        for($i = 0; $i < 12; $i++) {
            $fbclid .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $fbclid;
    }

    private function getRandomSource() {
        $sources = [
            'feed_timeline',
            'group_post',
            'story_view',
            'reels_feed',
            'suggested_post',
            'search_results',
            'notification',
            'instant_games',
            'profile_feed'
        ];
        return $sources[array_rand($sources)];
    }

    private function generateFBRef() {
        $types = [
            'feed', 'group', 'story', 'reel',
            'notification', 'search', 'bookmark',
            'profile', 'shortcut'
        ];
        return $types[array_rand($types)] . '_' . bin2hex(random_bytes(8));
    }

    private function getRandomLanguage() {
        $languages = [
            'en-US,en;q=0.9',
            'en-GB,en;q=0.8',
            'id-ID,id;q=0.9,en;q=0.8',
            'en-PH,en;q=0.9',
            'ms-MY,ms;q=0.9,en;q=0.8',
            'th-TH,th;q=0.9,en;q=0.8',
            'vi-VN,vi;q=0.9,en;q=0.8',
            'zh-CN,zh;q=0.9,en;q=0.8'
        ];
        return $languages[array_rand($languages)];
    }

    private function addRandomDelay() {
        $baseDelay = mt_rand(400000, 1200000);
        $jitter = mt_rand(-50000, 50000);
        usleep($baseDelay + $jitter);
    }

    private function appendParams($url, $params) {
        $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        return $url . $separator . http_build_query($params);
    }
}
?>
