<?php
error_reporting(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

class AnonimSMSAPI {
    private $apiEndpoints = [];
    private $proxies = [];
    private $userAgents = [];
    private $sentCount = 0;
    private $successCount = 0;
    private $failCount = 0;
    private $rateLimited = [];
    private $dbFile = 'sms_logs.json';
    private $proxyFile = 'proxies.txt';
    private $logs = [];
    private $activeThreads = 0;
    private $maxThreads = 20;
    private $defaultCountry = '90';
    private $workingProxies = [];
    private $failedProxies = [];
    private $proxyStats = [];
    
    public function __construct() {
        $this->initializeUserAgents();
        $this->initializeProxies();
        $this->initializeEndpoints();
        $this->loadLogs();
        $this->testProxies();
    }
    
    private function initializeUserAgents() {
        $this->userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (Android 14; Mobile; rv:121.0) Gecko/121.0 Firefox/121.0',
            'Dalvik/2.1.0 (Linux; U; Android 14; SM-S918B Build/UP1A)',
            'okhttp/4.9.0',
            'Apache-HttpClient/4.5.13',
            'Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36',
            'Mozilla/5.0 (Linux; Android 12; Pixel 6) AppleWebKit/537.36',
            'Mozilla/5.0 (Linux; Android 11; Redmi Note 10) AppleWebKit/537.36'
        ];
    }
    
    private function initializeProxies() {
        $proxyList = [
            "12.50.107.221:80", "8.220.141.8:89", "120.79.217.196:8082", "39.104.16.201:9080",
            "47.116.210.163:8123", "8.211.49.86:6666", "47.109.110.100:8080", "8.213.197.208:8889",
            "47.250.155.254:4145", "8.212.165.164:4369", "47.108.159.113:8080", "47.252.18.37:8834",
            "8.220.205.172:9080", "47.74.46.81:31433", "8.213.156.191:194", "101.200.158.109:9080",
            "39.104.23.154:8443", "47.238.60.156:4002", "8.138.125.130:9098", "47.250.11.111:3128",
            "8.213.195.191:9091", "8.211.51.115:9098", "8.137.38.48:3128", "47.252.11.233:5060",
            "8.211.194.85:8081", "47.120.0.231:3128", "39.104.27.89:9080", "47.238.60.156:18081",
            "39.102.213.50:9098", "8.212.168.170:9098", "8.213.195.191:8081", "8.220.204.92:18081",
            "47.238.134.126:8080", "103.118.44.164:8080", "8.215.15.163:8080", "47.112.19.200:8080",
            "8.213.222.157:3129", "8.213.215.187:8443", "47.251.87.199:8081", "8.220.205.172:9000",
            "47.238.60.156:81", "47.91.29.151:194", "8.212.165.164:5094", "47.116.126.57:3128",
            "8.148.24.225:30005", "8.219.229.53:3129", "39.104.23.154:6379", "47.250.11.111:8181",
            "47.104.27.249:8001", "47.92.152.43:8118", "185.135.69.34:80", "8.134.149.133:8800",
            "8.213.195.191:8095", "8.211.195.173:1080", "8.211.195.173:28737", "47.76.144.139:8081",
            "47.91.120.190:8888", "8.148.24.225:3128", "8.148.22.214:8008", "47.251.87.74:8081",
            "8.212.151.166:1234", "139.196.175.68:80", "47.251.73.54:92", "8.212.165.164:5000",
            "8.220.204.92:1081", "47.92.219.102:9098", "39.104.59.56:80", "149.129.226.9:1080",
            "47.106.75.125:8080", "47.254.36.213:5060", "47.91.89.3:9050", "8.213.222.157:1080",
            "8.213.215.187:30000", "47.91.29.151:1311", "47.238.128.246:8081", "121.43.154.123:8080",
            "8.213.128.6:8081", "8.211.195.173:2873", "47.237.92.86:50", "47.76.144.139:8080",
            "193.43.159.200:80", "47.74.46.81:10000", "47.250.177.202:20000", "8.213.222.247:20000",
            "8.138.149.37:8443", "39.102.211.162:8090", "47.104.28.135:8888", "47.238.130.212:8004",
            "8.130.74.114:9098", "149.129.226.9:80", "47.104.27.249:18080", "8.213.197.208:8080",
            "8.221.138.111:10002", "8.211.194.78:31433", "39.102.214.199:10000", "8.211.194.78:8005",
            "119.148.36.65:9108", "47.90.167.27:1080", "8.220.204.215:4145", "47.74.46.81:6606",
            "47.251.87.199:8181", "8.215.15.163:111", "8.211.194.85:80", "139.224.195.153:3129",
            "47.250.159.65:3128", "39.102.210.222:8081", "8.211.195.139:8000", "47.251.87.74:1036",
            "47.237.2.245:443", "47.104.27.165:8443", "47.91.29.151:33427", "8.211.194.78:5800",
            "8.213.134.213:1080", "47.252.11.233:10", "47.90.149.238:80", "8.213.197.208:9080",
            "39.102.211.64:5060", "8.138.82.6:80", "8.211.42.167:3333", "12.50.107.220:80",
            "8.211.49.86:5007", "39.102.213.3:3129", "47.254.36.213:8000", "47.252.11.233:443",
            "139.224.186.221:8088", "8.210.17.35:80", "8.220.204.92:90", "47.251.87.199:9080",
            "8.220.205.172:3129", "82.138.42.48:443", "39.102.209.121:50000", "39.102.208.149:81",
            "47.91.120.190:3128", "47.91.89.3:193", "8.130.37.235:3128", "47.76.144.139:4006",
            "47.90.167.27:5060", "8.220.141.8:9090", "8.211.49.86:100", "8.213.156.191:8080",
            "8.130.71.75:80", "8.209.96.245:31433", "39.102.208.23:3128", "119.18.145.241:20326",
            "47.108.159.113:8443", "39.102.208.189:8443", "47.119.19.221:9098", "8.215.12.103:8008",
            "47.91.109.17:6969", "47.99.112.148:1337", "121.43.146.222:9080", "8.220.136.174:3120",
            "8.213.128.6:5000", "47.121.133.212:9200", "47.121.183.107:20000", "39.102.210.176:8080",
            "47.92.219.102:9080", "8.137.38.48:80", "47.237.107.41:3129", "47.238.130.212:4145",
            "8.215.12.103:8006", "39.102.213.3:8443", "47.251.73.54:9050", "39.102.208.149:9080",
            "39.102.208.189:9080", "39.102.213.187:8000", "8.210.17.35:8081", "8.211.42.167:8080",
            "8.138.133.207:8081", "8.138.125.130:8080", "47.250.159.65:80", "8.221.139.222:20",
            "8.148.22.214:20002", "47.252.18.37:5060", "47.90.149.238:1111", "8.148.23.165:1234",
            "47.92.143.92:8080", "47.121.182.88:8090", "149.129.255.179:6666", "195.158.8.123:3128",
            "8.130.90.177:3128", "156.244.11.6:8080", "39.104.57.33:80", "39.102.213.187:3128",
            "8.210.17.35:8445", "202.5.35.93:20326", "178.212.144.7:80", "47.92.152.43:9098",
            "8.213.215.187:5060", "47.121.182.88:8081", "47.238.130.212:80", "8.130.54.67:9098",
            "185.235.16.12:80", "106.14.91.83:4000", "47.119.22.92:10000", "8.211.42.167:8083",
            "8.211.200.183:85", "47.250.159.65:9098", "8.137.62.53:8081", "47.90.149.238:5060",
            "47.109.83.196:8080", "47.99.112.148:8800", "103.86.109.38:80", "103.178.23.6:8080",
            "156.244.11.6:2000", "101.132.249.207:6588", "139.224.195.153:8008", "149.129.226.9:194",
            "8.213.222.157:8080", "47.237.113.119:8443", "8.212.168.170:8082", "47.119.22.156:9080",
            "39.102.214.208:8080", "47.237.113.119:1720", "39.104.59.56:8081", "8.211.194.85:45",
            "8.213.134.213:30000", "8.220.141.8:50", "8.211.200.183:1000", "47.100.73.178:45",
            "8.138.149.37:80", "39.102.208.23:8888", "8.211.195.139:6379", "47.104.27.165:8080",
            "47.90.167.27:8081", "47.91.110.148:1000", "8.130.37.235:1081", "47.251.73.54:3128",
            "47.250.155.254:8081", "27.34.242.98:80", "39.104.23.154:8088", "8.130.36.163:8080",
            "47.91.109.17:3128", "8.215.15.163:5060", "8.130.37.235:8081", "120.25.189.254:100",
            "47.91.109.17:20000", "39.104.27.89:8004"
        ];
        
        foreach ($proxyList as $proxy) {
            $this->proxies[] = [
                'address' => $proxy,
                'type' => $this->detectProxyType($proxy),
                'success' => 0,
                'fail' => 0,
                'last_used' => 0,
                'avg_response' => 0,
                'country' => $this->detectCountry($proxy)
            ];
        }
    }
    
    private function detectProxyType($proxy) {
        $port = explode(':', $proxy)[1] ?? '';
        $socksPorts = ['1080', '1081', '10808', '1085', '9050', '4145'];
        $httpPorts = ['80', '8080', '3128', '8888', '8000', '8081', '8082', '8090', '9080', '9090', '9098'];
        
        if (in_array($port, $socksPorts)) {
            return CURLPROXY_SOCKS5;
        } elseif (in_array($port, $httpPorts)) {
            return CURLPROXY_HTTP;
        } else {
            return CURLPROXY_HTTP;
        }
    }
    
    private function detectCountry($proxy) {
        $ip = explode(':', $proxy)[0];
        $ipParts = explode('.', $ip);
        
        if ($ipParts[0] == '47' || $ipParts[0] == '8' || $ipParts[0] == '39' || $ipParts[0] == '120' || $ipParts[0] == '101') {
            return 'CN';
        } elseif ($ipParts[0] == '12') {
            return 'US';
        } elseif ($ipParts[0] == '185' || $ipParts[0] == '178' || $ipParts[0] == '82') {
            return 'EU';
        } elseif ($ipParts[0] == '103' || $ipParts[0] == '119' || $ipParts[0] == '202') {
            return 'AS';
        } else {
            return 'Unknown';
        }
    }
    
    private function testProxies() {
        $testUrl = 'http://httpbin.org/ip';
        $multiCurl = curl_multi_init();
        $handles = [];
        $testResults = [];
        
        $testBatch = array_slice($this->proxies, 0, 50);
        
        foreach ($testBatch as $index => $proxy) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['address']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type']);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgents[array_rand($this->userAgents)]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            
            curl_multi_add_handle($multiCurl, $ch);
            $handles[$index] = $ch;
        }
        
        $running = null;
        do {
            curl_multi_exec($multiCurl, $running);
            curl_multi_select($multiCurl);
        } while ($running > 0);
        
        foreach ($handles as $index => $ch) {
            $response = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            
            if ($error == '' && $info['http_code'] == 200) {
                $this->proxies[$index]['success'] = 1;
                $this->proxies[$index]['avg_response'] = $info['total_time'];
                $this->workingProxies[] = $this->proxies[$index];
            } else {
                $this->proxies[$index]['fail'] = 1;
                $this->failedProxies[] = $this->proxies[$index];
            }
            
            curl_multi_remove_handle($multiCurl, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($multiCurl);
    }
    
    private function initializeEndpoints() {
        $this->apiEndpoints = [
            [
                'name' => 'TextBelt',
                'url' => 'https://textbelt.com/text',
                'method' => 'POST',
                'data' => ['phone' => '{numara}', 'message' => '{mesaj}', 'key' => 'textbelt'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => 'success":true',
                'country' => 'INT'
            ],
            [
                'name' => 'FreeSMSGateway',
                'url' => 'https://www.freesmsgateway.com/api/send',
                'method' => 'POST',
                'data' => ['to' => '{numara}', 'msg' => '{mesaj}', 'api_key' => 'test'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => '"status":"sent"',
                'country' => 'INT'
            ],
            [
                'name' => 'SMSGlobal',
                'url' => 'https://api.smsglobal.com/v1/sms/',
                'method' => 'POST',
                'data' => ['destination' => '{numara}', 'message' => '{mesaj}'],
                'headers' => ['Content-Type: application/json', 'Authorization: Basic dGVzdDp0ZXN0'],
                'success_pattern' => '"accepted"',
                'country' => 'INT'
            ],
            [
                'name' => 'BulkSMS',
                'url' => 'https://api.bulksms.com/v1/messages',
                'method' => 'POST',
                'data' => ['to' => '{numara}', 'body' => '{mesaj}'],
                'headers' => ['Content-Type: application/json', 'Authorization: Basic ' . base64_encode('test:test')],
                'success_pattern' => '"id"',
                'country' => 'INT'
            ],
            [
                'name' => 'TwilioTest',
                'url' => 'https://api.twilio.com/2010-04-01/Accounts/test/Messages.json',
                'method' => 'POST',
                'data' => ['To' => '{numara}', 'From' => '+15005550006', 'Body' => '{mesaj}'],
                'headers' => ['Content-Type: application/x-www-form-urlencoded'],
                'auth' => ['test', 'test'],
                'success_pattern' => '"status": "queued"',
                'country' => 'US'
            ],
            [
                'name' => 'Nexmo',
                'url' => 'https://rest.nexmo.com/sms/json',
                'method' => 'POST',
                'data' => ['api_key' => 'test', 'api_secret' => 'test', 'to' => '{numara}', 'from' => 'Test', 'text' => '{mesaj}'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => '"status":"0"',
                'country' => 'INT'
            ],
            [
                'name' => 'Plivo',
                'url' => 'https://api.plivo.com/v1/Account/test/Message/',
                'method' => 'POST',
                'data' => ['src' => 'Test', 'dst' => '{numara}', 'text' => '{mesaj}'],
                'headers' => ['Content-Type: application/json'],
                'auth' => ['test', 'test'],
                'success_pattern' => '"message_uuid"',
                'country' => 'INT'
            ],
            [
                'name' => 'MessageBird',
                'url' => 'https://rest.messagebird.com/messages',
                'method' => 'POST',
                'data' => ['recipients' => '{numara}', 'originator' => 'Test', 'body' => '{mesaj}'],
                'headers' => ['Content-Type: application/json', 'Authorization: AccessKey test'],
                'success_pattern' => '"id"',
                'country' => 'INT'
            ],
            [
                'name' => 'ClickSend',
                'url' => 'https://rest.clicksend.com/v3/sms/send',
                'method' => 'POST',
                'data' => ['messages' => [['to' => '{numara}', 'from' => 'Test', 'body' => '{mesaj}']]],
                'headers' => ['Content-Type: application/json'],
                'auth' => ['test', 'test'],
                'success_pattern' => '"http_code":200',
                'country' => 'INT'
            ],
            [
                'name' => 'SMS77',
                'url' => 'https://gateway.sms77.io/api/sms',
                'method' => 'POST',
                'data' => ['to' => '{numara}', 'text' => '{mesaj}', 'from' => 'Test'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => '"success":true',
                'country' => 'DE'
            ],
            [
                'name' => 'TurkcellOTP',
                'url' => 'https://odeme.turkcell.com.tr/api/sendotp',
                'method' => 'POST',
                'data' => ['msisdn' => '90{numara}', 'type' => 'sms'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => 'success',
                'country' => 'TR'
            ],
            [
                'name' => 'VodafoneOTP',
                'url' => 'https://www.vodafone.com.tr/api/auth/send-otp',
                'method' => 'POST',
                'data' => ['phoneNumber' => '90{numara}', 'channel' => 'SMS'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => 'success',
                'country' => 'TR'
            ],
            [
                'name' => 'TurkTelekomOTP',
                'url' => 'https://www.turktelekom.com.tr/api/auth/sendOTP',
                'method' => 'POST',
                'data' => ['gsmNumber' => '90{numara}', 'type' => 'sms'],
                'headers' => ['Content-Type: application/json'],
                'success_pattern' => 'success',
                'country' => 'TR'
            ]
        ];
    }
    
    private function loadLogs() {
        if (file_exists($this->dbFile)) {
            $data = json_decode(file_get_contents($this->dbFile), true);
            $this->logs = $data['logs'] ?? [];
            $this->sentCount = $data['sent'] ?? 0;
            $this->successCount = $data['success'] ?? 0;
            $this->failCount = $data['fail'] ?? 0;
        }
    }
    
    private function saveLogs() {
        $data = [
            'logs' => array_slice($this->logs, -1000),
            'sent' => $this->sentCount,
            'success' => $this->successCount,
            'fail' => $this->failCount,
            'last_update' => time()
        ];
        file_put_contents($this->dbFile, json_encode($data));
    }
    
    private function formatPhoneNumber($number, $country = '90') {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (substr($number, 0, 2) == '00') {
            $number = substr($number, 2);
        }
        
        if (substr($number, 0, 1) == '+') {
            $number = substr($number, 1);
        }
        
        if (strlen($number) == 10 && substr($number, 0, 1) == '5') {
            $number = $country . $number;
        }
        
        if (strlen($number) == 11 && substr($number, 0, 1) == '0') {
            $number = $country . substr($number, 1);
        }
        
        return $number;
    }
    
    private function sendSMSWithProxy($endpoint, $phone, $message, $proxy) {
        $url = $endpoint['url'];
        $data = $endpoint['data'];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = str_replace('{numara}', $phone, $value);
                $data[$key] = str_replace('{mesaj}', $message, $data[$key]);
            }
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_PROXY, $proxy['address']);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgents[array_rand($this->userAgents)]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        
        if ($endpoint['method'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        
        $headers = $endpoint['headers'] ?? [];
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        $headers[] = 'Connection: keep-alive';
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if (isset($endpoint['auth'])) {
            curl_setopt($ch, CURLOPT_USERPWD, $endpoint['auth'][0] . ':' . $endpoint['auth'][1]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        $success = false;
        if ($error == '' && $httpCode >= 200 && $httpCode < 300) {
            if (strpos($response, $endpoint['success_pattern']) !== false) {
                $success = true;
            }
        }
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'error' => $error,
            'response' => substr($response, 0, 200),
            'time' => $info['total_time']
        ];
    }
    
    public function sendSMS($phone, $message, $count = 1, $country = '90') {
        $phone = $this->formatPhoneNumber($phone, $country);
        
        if (strlen($phone) < 10) {
            return ['status' => 'error', 'message' => 'Geçersiz telefon numarası'];
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        $workingProxies = array_filter($this->proxies, function($p) {
            return $p['success'] > $p['fail'] * 2;
        });
        
        if (empty($workingProxies)) {
            $workingProxies = $this->proxies;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $endpoint = $this->apiEndpoints[array_rand($this->apiEndpoints)];
            $proxy = $workingProxies[array_rand($workingProxies)];
            
            $result = $this->sendSMSWithProxy($endpoint, $phone, $message, $proxy);
            
            $proxyIndex = array_search($proxy, $this->proxies);
            if ($proxyIndex !== false) {
                if ($result['success']) {
                    $this->proxies[$proxyIndex]['success']++;
                    $successCount++;
                    $this->successCount++;
                } else {
                    $this->proxies[$proxyIndex]['fail']++;
                    $failCount++;
                    $this->failCount++;
                }
                $this->proxies[$proxyIndex]['last_used'] = time();
            }
            
            $results[] = [
                'api' => $endpoint['name'],
                'proxy' => $proxy['address'],
                'success' => $result['success'],
                'http_code' => $result['http_code'],
                'time' => $result['time']
            ];
            
            $this->sentCount++;
            
            usleep(500000);
        }
        
        $logEntry = [
            'time' => time(),
            'phone' => substr($phone, -6),
            'message' => substr($message, 0, 20),
            'count' => $count,
            'success' => $successCount,
            'fail' => $failCount
        ];
        
        $this->logs[] = $logEntry;
        $this->saveLogs();
        
        return [
            'status' => 'completed',
            'phone' => $phone,
            'requested' => $count,
            'success' => $successCount,
            'fail' => $failCount,
            'details' => $results
        ];
    }
    
    public function sendBulk($numbers, $message, $country = '90') {
        $results = [];
        $totalSuccess = 0;
        $totalFail = 0;
        
        $numberArray = explode(',', $numbers);
        $numberArray = array_map('trim', $numberArray);
        
        foreach ($numberArray as $number) {
            if (empty($number)) continue;
            
            $result = $this->sendSMS($number, $message, 1, $country);
            $totalSuccess += $result['success'];
            $totalFail += $result['fail'];
            $results[] = $result;
            
            usleep(1000000);
        }
        
        return [
            'status' => 'bulk_completed',
            'total_numbers' => count($numberArray),
            'total_success' => $totalSuccess,
            'total_fail' => $totalFail,
            'results' => $results
        ];
    }
    
    public function getStats() {
        $validProxies = count(array_filter($this->proxies, function($p) {
            return $p['success'] > 0;
        }));
        
        return [
            'total_sent' => $this->sentCount,
            'total_success' => $this->successCount,
            'total_fail' => $this->failCount,
            'success_rate' => $this->sentCount > 0 ? round(($this->successCount / $this->sentCount) * 100, 2) . '%' : '0%',
            'total_proxies' => count($this->proxies),
            'working_proxies' => count($this->workingProxies),
            'valid_proxies' => $validProxies,
            'failed_proxies' => count($this->failedProxies),
            'recent_logs' => array_slice($this->logs, -10)
        ];
    }
    
    public function getProxies() {
        return [
            'total' => count($this->proxies),
            'working' => $this->workingProxies,
            'failed' => array_slice($this->failedProxies, 0, 10)
        ];
    }
    
    public function testProxy($proxyAddress) {
        foreach ($this->proxies as $index => $proxy) {
            if ($proxy['address'] == $proxyAddress) {
                $testUrl = 'http://httpbin.org/ip';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $testUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_PROXY, $proxy['address']);
                curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgents[array_rand($this->userAgents)]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                
                $response = curl_exec($ch);
                $info = curl_getinfo($ch);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error == '' && $info['http_code'] == 200) {
                    return ['status' => 'working', 'time' => $info['total_time'], 'response' => json_decode($response, true)];
                } else {
                    return ['status' => 'failed', 'error' => $error];
                }
            }
        }
        return ['status' => 'not_found'];
    }
}

header('Content-Type: application/json');

$sms = new AnonimSMSAPI();

$action = $_GET['action'] ?? 'send';
$phone = $_GET['phone'] ?? $_POST['phone'] ?? '';
$message = $_GET['message'] ?? $_POST['message'] ?? '';
$count = intval($_GET['count'] ?? $_POST['count'] ?? 1);
$country = $_GET['country'] ?? $_POST['country'] ?? '90';
$numbers = $_GET['numbers'] ?? $_POST['numbers'] ?? '';
$proxy = $_GET['proxy'] ?? '';

switch ($action) {
    case 'send':
        if (empty($phone) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Telefon ve mesaj gerekli']);
            break;
        }
        $result = $sms->sendSMS($phone, $message, $count, $country);
        echo json_encode($result, JSON_PRETTY_PRINT);
        break;
        
    case 'bulk':
        if (empty($numbers) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Numaralar ve mesaj gerekli']);
            break;
        }
        $result = $sms->sendBulk($numbers, $message, $country);
        echo json_encode($result, JSON_PRETTY_PRINT);
        break;
        
    case 'stats':
        echo json_encode($sms->getStats(), JSON_PRETTY_PRINT);
        break;
        
    case 'proxies':
        echo json_encode($sms->getProxies(), JSON_PRETTY_PRINT);
        break;
        
    case 'test_proxy':
        if (empty($proxy)) {
            echo json_encode(['status' => 'error', 'message' => 'Proxy adresi gerekli']);
            break;
        }
        $result = $sms->testProxy($proxy);
        echo json_encode($result, JSON_PRETTY_PRINT);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz action'], JSON_PRETTY_PRINT);
}
?>
