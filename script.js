document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const downloadForm = document.getElementById('download-form');
    const videoUrlInput = document.getElementById('video-url');
    const resultBox = document.getElementById('result-box');
    const loader = document.getElementById('loader');
    const errorMessage = document.getElementById('error-message');
    const themeIcon = document.getElementById('theme-icon');
    
    // Video Elements
    const videoThumbnail = document.getElementById('video-thumbnail');
    const videoTitle = document.getElementById('video-title');
    const videoAuthor = document.getElementById('video-author');
    const likeCount = document.getElementById('like-count');
    const commentCount = document.getElementById('comment-count');
    const shareCount = document.getElementById('share-count');
    const downloadVideoBtn = document.getElementById('download-video');
    const downloadAudioBtn = document.getElementById('download-audio');

    // Check for saved theme preference
    if (localStorage.getItem('darkTheme') === 'enabled') {
        document.body.classList.add('dark-theme');
        themeIcon.classList.replace('fa-moon', 'fa-sun');
    }

    // Toggle theme
    themeIcon.addEventListener('click', function() {
        document.body.classList.toggle('dark-theme');
        
        if (document.body.classList.contains('dark-theme')) {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('darkTheme', 'enabled');
        } else {
            themeIcon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('darkTheme', 'disabled');
        }
    });

    // Handle form submission
    downloadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const videoUrl = videoUrlInput.value.trim();
        
        if (!isValidTikTokUrl(videoUrl)) {
            showError('Lütfen geçerli bir TikTok video URL\'si girin.');
            return;
        }
        
        // Hide previous results and errors, show loader
        resultBox.style.display = 'none';
        errorMessage.style.display = 'none';
        loader.style.display = 'flex';
        
        // Process the video URL
        fetchVideoInfo(videoUrl);
    });

    // Validate TikTok URL
    function isValidTikTokUrl(url) {
        const pattern = /^(https?:\/\/)?(www\.|vm\.)?tiktok\.com\/(@[\w.-]+\/video\/\d+|\w+\/|v\/\d+|@[\w.-]+|\w+)/;
        return pattern.test(url);
    }

    // Fetch video information
    function fetchVideoInfo(videoUrl) {
        // Format URL to ensure proper structure
        const formattedUrl = formatTikTokUrl(videoUrl);
        
        // Create form data for AJAX request
        const formData = new FormData();
        formData.append('video-url', formattedUrl);
        formData.append('no-watermark', document.getElementById('no-watermark').checked);
        formData.append('hd-quality', document.getElementById('hd-quality').checked);
        
        // Hide previous results and errors, show loader
        resultBox.style.display = 'none';
        errorMessage.style.display = 'none';
        loader.style.display = 'flex';
        
        // Send AJAX request to PHP backend
        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Sunucu hatası: ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                showError(data.error);
                
                // Yine de yükleme yapmayı deneyelim - kullanıcı geri bildirim ister
                setTimeout(() => {
                    const tryAgain = confirm("Video bilgileri alınamadı. Başka bir TikTok indirme yöntemi denemek ister misiniz?");
                    if (tryAgain) {
                        // Manuel olarak video indirme URL'si oluştur
                        const videoId = extractVideoId(videoUrl);
                        if (videoId) {
                            createFallbackDownload(videoId);
                        } else {
                            showError("Video ID bulunamadı. Lütfen geçerli bir TikTok URL'si girdiğinizden emin olun.");
                        }
                    }
                }, 500);
            } else {
                displayVideoInfo(data);
            }
        })
        .catch(error => {
            showError(error.message);
            console.error("API Hatası:", error);
        })
        .finally(() => {
            loader.style.display = 'none';
        });
    }
    
    // TikTok video ID'sini çıkar
    function extractVideoId(url) {
        const regex = /\/video\/(\d+)/;
        const match = url.match(regex);
        return match ? match[1] : null;
    }
    
    // Alternatif indirme yöntemini dene
    function createFallbackDownload(videoId) {
        // Video ve ses göster
        resultBox.style.display = 'block';
        
        // Düşük kaliteli önizleme resmi ayarla
        videoThumbnail.src = `https://p16-sign-va.tiktokcdn.com/obj/tos-maliva-p-0068/${videoId}.jpg`;
        videoTitle.textContent = "TikTok Video";
        videoAuthor.textContent = "Kullanıcı bilgisi yüklenemedi";
        
        // Sayaçları sıfırla
        likeCount.textContent = "N/A";
        commentCount.textContent = "N/A";
        shareCount.textContent = "N/A";
        
        // Alternatif bir indirme bağlantısı oluştur
        const alternativeVideoUrl = `https://www.tikwm.com/video/media/hdplay/${videoId}.mp4`;
        downloadVideoBtn.href = alternativeVideoUrl;
        downloadVideoBtn.setAttribute('download', `tiktok_video_${videoId}.mp4`);
        
        // Ses bulunamadı
        downloadAudioBtn.style.display = 'none';
    }

    // Format TikTok URL (ensure consistent format)
    function formatTikTokUrl(url) {
        // If URL has a '?' character, remove everything after it
        if (url.includes('?')) {
            url = url.split('?')[0];
        }
        
        // If URL doesn't start with http, add it
        if (!url.startsWith('http')) {
            url = 'https://' + url;
        }
        
        return url;
    }

    // Display error message
    function showError(message) {
        errorMessage.querySelector('p').textContent = message;
        errorMessage.style.display = 'flex';
        loader.style.display = 'none';
        resultBox.style.display = 'none';
    }

    // Display video information
    function displayVideoInfo(data) {
        // Set video details
        videoThumbnail.src = data.thumbnail;
        videoTitle.textContent = data.title || 'TikTok Video';
        videoAuthor.textContent = data.author || '@username';
        
        // Format numbers
        likeCount.textContent = formatNumber(data.likes);
        commentCount.textContent = formatNumber(data.comments);
        shareCount.textContent = formatNumber(data.shares);
        
        // Set download links
        downloadVideoBtn.href = data.videoUrl;
        downloadVideoBtn.setAttribute('download', `tiktok_video_${Date.now()}.mp4`);
        
        downloadAudioBtn.href = data.audioUrl;
        downloadAudioBtn.setAttribute('download', `tiktok_audio_${Date.now()}.mp3`);
        
        // Show result box
        resultBox.style.display = 'block';
    }

    // Format numbers (e.g., 1000 -> 1K)
    function formatNumber(num) {
        if (!num) return '0';
        
        num = parseInt(num);
        
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        } else {
            return num.toString();
        }
    }

    // Input validation and auto-formatting
    videoUrlInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            const pastedText = videoUrlInput.value.trim();
            videoUrlInput.value = formatTikTokUrl(pastedText);
        }, 0);
    });
});