<?php
// Hata raporlamasını başlat
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS ayarları (geliştirme için)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// POST isteği kontrolü
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Sadece POST istekleri kabul edilir."]);
        exit;
    }
/**
 * Kullanıcı bilgilerini getirir
 * 
 * @param string $username Kullanıcı adı
 * @param string $apiKey API anahtarı
 * @param string $apiHost API host
 * @return array Kullanıcı bilgileri
 * @throws Exception API hatası durumunda
 */
function getUserInfo($username, $apiKey, $apiHost) {
    // Kullanıcı bilgileri API URL'si
    $apiUrl = "https://tiktok-api23.p.rapidapi.com/api/user/info";
    
    // cURL isteği başlat
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl . "?uniqueId=" . urlencode($username),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . $apiHost,
            "x-rapidapi-key: " . $apiKey
        ],
    ]);
    
    // İsteği gönder ve yanıtı al
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    // cURL isteğini kapat
    curl_close($curl);
    
    // Hata kontrolü
    if ($err) {
        throw new Exception("Kullanıcı Bilgileri cURL Hatası: " . $err);
    }
    
    // JSON yanıtını çöz
    $result = json_decode($response, true);
    
    // API yanıt kontrolü
    if (!$result || isset($result['error']) || !isset($result['data'])) {
        $errorMessage = isset($result['message']) ? $result['message'] : "Kullanıcı bilgileri API yanıt hatası.";
        throw new Exception("TikTok Kullanıcı API Hatası: " . $errorMessage);
    }
    
    // Kullanıcı bilgilerini döndür
    return [
        "user" => isset($result['data']['user']) ? $result['data']['user'] : [],
        "nickname" => isset($result['data']['user']['nickname']) ? $result['data']['user']['nickname'] : "",
        "bio" => isset($result['data']['user']['signature']) ? $result['data']['user']['signature'] : "",
        "avatar" => isset($result['data']['user']['avatarLarger']) ? $result['data']['user']['avatarLarger'] : "",
        "statistics" => isset($result['data']['stats']) ? $result['data']['stats'] : []
    ];
}

/**
 * Video detaylarını getirir
 * 
 * @param string $videoId Video ID
 * @param string $apiKey API anahtarı
 * @param string $apiHost API host
 * @return array Video detayları
 * @throws Exception API hatası durumunda
 */
function getVideoDetails($videoId, $apiKey, $apiHost) {
    // Video detayları API URL'si
    $apiUrl = "https://tiktok-api23.p.rapidapi.com/api/post/detail";
    
    // cURL isteği başlat
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl . "?videoId=" . urlencode($videoId),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . $apiHost,
            "x-rapidapi-key: " . $apiKey
        ],
    ]);
    
    // İsteği gönder ve yanıtı al
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    // cURL isteğini kapat
    curl_close($curl);
    
    // Hata kontrolü
    if ($err) {
        throw new Exception("Video Detayları cURL Hatası: " . $err);
    }
    
    // JSON yanıtını çöz
    $result = json_decode($response, true);
    
    // API yanıt kontrolü
    if (!$result || isset($result['error']) || !isset($result['data'])) {
        $errorMessage = isset($result['message']) ? $result['message'] : "Video detayları API yanıt hatası.";
        throw new Exception("TikTok Video Detayları API Hatası: " . $errorMessage);
    }
    
    // Video detaylarını döndür
    return [
        "title" => isset($result['data']['desc']) ? $result['data']['desc'] : "",
        "thumbnail" => isset($result['data']['video']['cover']) ? $result['data']['video']['cover'] : "",
        "likes" => isset($result['data']['stats']['diggCount']) ? $result['data']['stats']['diggCount'] : 0,
        "comments" => isset($result['data']['stats']['commentCount']) ? $result['data']['stats']['commentCount'] : 0,
        "shares" => isset($result['data']['stats']['shareCount']) ? $result['data']['stats']['shareCount'] : 0,
        "video" => isset($result['data']['video']) ? $result['data']['video'] : [],
        "author" => isset($result['data']['author']) ? $result['data']['author'] : []
    ];
}

// Video URL kontrolü
if (!isset($_POST["video-url"]) || empty($_POST["video-url"])) {
    echo json_encode(["error" => "Video URL'si gereklidir."]);
    exit;
}

// POST verilerini al
$videoUrl = $_POST["video-url"];
$noWatermark = isset($_POST["no-watermark"]) ? filter_var($_POST["no-watermark"], FILTER_VALIDATE_BOOLEAN) : true;
$hdQuality = isset($_POST["hd-quality"]) ? filter_var($_POST["hd-quality"], FILTER_VALIDATE_BOOLEAN) : true;

// TikTok URL'sinin geçerliliğini kontrol et
if (!preg_match('/^(https?:\/\/)?(www\.|vm\.)?tiktok\.com\/(@[\w.-]+\/video\/\d+|\w+\/|v\/\d+|@[\w.-]+|\w+)/', $videoUrl)) {
    echo json_encode(["error" => "Geçersiz TikTok URL'si."]);
    exit;
}

// TikTok API'sinden video ve ses bilgilerini al
try {
    // Ana API ile deneyin
    try {
        $videoInfo = getTikTokVideoAndAudioInfo($videoUrl, $noWatermark, $hdQuality);
        echo json_encode($videoInfo);
    } catch (Exception $e) {
        // Ana API başarısız olursa, alternatif API'yi dene
        error_log("Ana API başarısız oldu: " . $e->getMessage() . ". Alternatif API deneniyor...");
        $videoInfo = getTikTokVideoAlternativeAPI($videoUrl);
        echo json_encode($videoInfo);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

/**
 * Alternatif API ile TikTok video bilgilerini çeker
 * 
 * @param string $videoUrl TikTok video URL'si
 * @return array Video bilgileri
 * @throws Exception API hatası durumunda
 */
function getTikTokVideoAlternativeAPI($videoUrl) {
    // Alternatif API URL'si - ücretsiz bir TikTok API servisi kullanabiliriz
    $apiUrl = "https://www.tikwm.com/api/";
    
    // POST verileri
    $postData = [
        "url" => $videoUrl,
        "hd" => 1
    ];
    
    // cURL isteği başlat
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);
    
    // İsteği gönder ve yanıtı al
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    
    // Hata ayıklama
    error_log("Alternatif API HTTP Kodu: " . $httpCode);
    error_log("Alternatif API Yanıt: " . $response);
    
    // cURL isteğini kapat
    curl_close($curl);
    
    // Hata kontrolü
    if ($err) {
        throw new Exception("Alternatif API cURL Hatası: " . $err);
    }
    
    // JSON yanıtını çöz
    $result = json_decode($response, true);
    
    // API yanıt kontrolü
    if (!$result || isset($result['error']) || !isset($result['data'])) {
        $errorMessage = isset($result['msg']) ? $result['msg'] : "Alternatif API yanıt hatası.";
        throw new Exception("Alternatif API Hatası: " . $errorMessage);
    }
    
    // Yanıt verilerini hazırla
    return [
        "title" => isset($result['data']['title']) ? $result['data']['title'] : "TikTok Video",
        "author" => isset($result['data']['author']['nickname']) ? $result['data']['author']['nickname'] : "",
        "thumbnail" => isset($result['data']['cover']) ? $result['data']['cover'] : "",
        "videoUrl" => isset($result['data']['play']) ? $result['data']['play'] : 
                    (isset($result['data']['wmplay']) ? $result['data']['wmplay'] : null),
        "audioUrl" => isset($result['data']['music']) ? $result['data']['music'] : null,
        "likes" => isset($result['data']['digg_count']) ? $result['data']['digg_count'] : 0,
        "comments" => isset($result['data']['comment_count']) ? $result['data']['comment_count'] : 0,
        "shares" => isset($result['data']['share_count']) ? $result['data']['share_count'] : 0,
        "userInfo" => isset($result['data']['author']) ? $result['data']['author'] : null
    ];
}

/**
 * TikTok API'sinden video ve ses bilgilerini çeker ve video detaylarını getirir
 * 
 * @param string $videoUrl TikTok video URL'si
 * @param bool $noWatermark Filigranlı indirme seçeneği
 * @param bool $hdQuality HD kalite seçeneği
 * @return array Video ve ses bilgileri
 * @throws Exception API hatası durumunda
 */
function getTikTokVideoAndAudioInfo($videoUrl, $noWatermark, $hdQuality) {
    // RapidAPI anahtarı
    $apiKey = "sizin-api-anahtariniz";
    $apiHost = "tiktok-api23.p.rapidapi.com";
    
    // Video bilgilerini al
    $videoData = getVideoData($videoUrl, $apiKey, $apiHost);
    
    // Ses bilgilerini al
    $audioData = getAudioData($videoUrl, $apiKey, $apiHost);
    
    // Video detaylarını al (isteğe bağlı - video ID'si varsa)
    $videoDetails = [];
    if (preg_match('/\/video\/(\d+)/', $videoUrl, $matches)) {
        $videoId = $matches[1];
        try {
            $videoDetails = getVideoDetails($videoId, $apiKey, $apiHost);
        } catch (Exception $e) {
            // Video detayları alınamazsa devam et
            error_log("Video detayları alınamadı: " . $e->getMessage());
        }
    }
    
    // Kullanıcı bilgilerini al (isteğe bağlı - kullanıcı adı varsa)
    $userData = [];
    if (preg_match('/@([\w\.-]+)/', $videoUrl, $matches)) {
        $username = $matches[1];
        try {
            $userData = getUserInfo($username, $apiKey, $apiHost);
        } catch (Exception $e) {
            // Kullanıcı bilgileri alınamazsa devam et
            error_log("Kullanıcı bilgileri alınamadı: " . $e->getMessage());
        }
    }
    
    // Tüm verileri birleştir ve döndür
    return array_merge(
        [
            "title" => isset($videoData['title']) ? $videoData['title'] : 
                      (isset($videoDetails['title']) ? $videoDetails['title'] : "TikTok Video"),
            "author" => isset($videoData['author']) ? $videoData['author'] : 
                       (isset($userData['nickname']) ? $userData['nickname'] : ""),
            "thumbnail" => isset($videoData['thumbnail']) ? $videoData['thumbnail'] : 
                         (isset($videoDetails['thumbnail']) ? $videoDetails['thumbnail'] : ""),
            "videoUrl" => isset($videoData['videoUrl']) ? $videoData['videoUrl'] : null,
            "audioUrl" => isset($audioData['audioUrl']) ? $audioData['audioUrl'] : null,
            "likes" => isset($videoData['likes']) ? $videoData['likes'] : 
                     (isset($videoDetails['likes']) ? $videoDetails['likes'] : 0),
            "comments" => isset($videoData['comments']) ? $videoData['comments'] : 
                        (isset($videoDetails['comments']) ? $videoDetails['comments'] : 0),
            "shares" => isset($videoData['shares']) ? $videoData['shares'] : 
                      (isset($videoDetails['shares']) ? $videoDetails['shares'] : 0),
        ],
        isset($userData['statistics']) ? ["userStats" => $userData['statistics']] : [],
        isset($userData['user']) ? ["userInfo" => $userData['user']] : []
    );
}

/**
 * Video verilerini çeker
 * 
 * @param string $videoUrl Video URL'si
 * @param string $apiKey API anahtarı
 * @param string $apiHost API host
 * @return array Video verileri
 * @throws Exception API hatası durumunda
 */
function getVideoData($videoUrl, $apiKey, $apiHost) {
    // Video indirme API URL'si
    $apiUrl = "https://tiktok-api23.p.rapidapi.com/api/download/video";
    
    // URL'yi kodla - urlencode fonksiyonu yerine rawurlencode kullan
    $encodedUrl = rawurlencode($videoUrl);
    
    // Hata ayıklama
    error_log("Encoded URL: " . $encodedUrl);
    error_log("API URL: " . $apiUrl . "?url=" . $encodedUrl);
    
    // cURL isteği başlat
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl . "?url=" . $encodedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . $apiHost,
            "x-rapidapi-key: " . $apiKey
        ],
    ]);
    
    // İsteği gönder ve yanıtı al
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    
    // Hata ayıklama - API yanıtını ve HTTP kodunu loga yazdır
    error_log("HTTP Code: " . $httpCode);
    error_log("Video API Yanıt: " . $response);
    
    // cURL isteğini kapat
    curl_close($curl);
    
    // Hata kontrolü
    if ($err) {
        throw new Exception("Video cURL Hatası: " . $err);
    }
    
    // HTTP durum kodu kontrolü
    if ($httpCode != 200) {
        throw new Exception("API HTTP Hata Kodu: " . $httpCode);
    }
    
    // JSON yanıtını çöz
    $result = json_decode($response, true);
    
    // API yanıt kontrolü - hata mesajlarını daha detaylı göster
    if (!$result) {
        throw new Exception("API yanıtı geçersiz JSON formatında.");
    }
    
    if (isset($result['error'])) {
        $errorMessage = isset($result['message']) ? $result['message'] : "Video API yanıt hatası.";
        throw new Exception("API Hata Detayı: " . $errorMessage . " (Kod: " . (isset($result['error']) ? $result['error'] : "Bilinmiyor") . ")");
    }
    
    if (!isset($result['data'])) {
        // Ham yanıtı hata detayı olarak göster
        throw new Exception("API yanıt yapısı beklenen formatta değil. Ham yanıt: " . substr($response, 0, 200) . "...");
    }
    
    // Video bilgilerini hazırla
    $videoData = [
        "videoUrl" => isset($result['data']['play']) ? $result['data']['play'] : null,
        "title" => isset($result['data']['title']) ? $result['data']['title'] : "",
        "author" => isset($result['data']['author']) ? $result['data']['author'] : "",
        "thumbnail" => isset($result['data']['cover']) ? $result['data']['cover'] : "",
        "likes" => isset($result['data']['digg_count']) ? $result['data']['digg_count'] : 0,
        "comments" => isset($result['data']['comment_count']) ? $result['data']['comment_count'] : 0,
        "shares" => isset($result['data']['share_count']) ? $result['data']['share_count'] : 0,
    ];
    
    // Video URL'si bulunamadıysa hata döndür
    if (!$videoData['videoUrl']) {
        throw new Exception("Video URL'si bulunamadı. API yanıtı: " . substr($response, 0, 200) . "...");
    }
    
    return $videoData;
}

/**
 * Ses verilerini çeker
 * 
 * @param string $videoUrl Video URL'si
 * @param string $apiKey API anahtarı
 * @param string $apiHost API host
 * @return array Ses verileri
 */
function getAudioData($videoUrl, $apiKey, $apiHost) {
    // Ses indirme API URL'si
    $apiUrl = "https://tiktok-api23.p.rapidapi.com/api/download/music";
    
    // URL'yi kodla
    $encodedUrl = urlencode($videoUrl);
    
    // cURL isteği başlat
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl . "?url=" . $encodedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . $apiHost,
            "x-rapidapi-key: " . $apiKey
        ],
    ]);
    
    // İsteği gönder ve yanıtı al
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    // Hata ayıklama - API yanıtını loga yazdır
    error_log("Ses API Yanıt: " . $response);
    
    // cURL isteğini kapat
    curl_close($curl);
    
    // Varsayılan ses verisi
    $audioData = [
        "audioUrl" => null
    ];
    
    // Hata yoksa ve yanıt geçerliyse, ses URL'sini ayarla
    if (!$err) {
        // JSON yanıtını çöz
        $result = json_decode($response, true);
        
        // Geçerli yanıt varsa
        if ($result && isset($result['data']) && isset($result['data']['music'])) {
            $audioData["audioUrl"] = $result['data']['music'];
        }
    }
    
    return $audioData;
}