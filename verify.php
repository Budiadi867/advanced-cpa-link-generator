<?php
session_start();
$token = $_GET['token'] ?? '';
$challenge = bin2hex(random_bytes(16));
$_SESSION['challenge'] = $challenge;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Required</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <p>Verifying your browser, please wait...</p>
    <script>
        (function() {
            function getBrowserData() {
                return {
                    userAgent: navigator.userAgent,
                    language: navigator.language,
                    languages: navigator.languages,
                    platform: navigator.platform,
                    hardwareConcurrency: navigator.hardwareConcurrency,
                    deviceMemory: navigator.deviceMemory,
                    connection: navigator.connection ? {
                        type: navigator.connection.effectiveType,
                        rtt: navigator.connection.rtt,
                        downlink: navigator.connection.downlink
                    } : null
                };
            }

            function getCanvasFingerprint() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = 200;
                canvas.height = 50;
                
                // Add text with different styles
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillStyle = '#1a73e8';
                ctx.fillText('Verification', 2, 2);
                
                // Add shapes
                ctx.fillStyle = '#e33d3d';
                ctx.beginPath();
                ctx.arc(50, 25, 10, 0, Math.PI * 2);
                ctx.fill();
                
                return canvas.toDataURL();
            }

            function getWebGLFingerprint() {
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl');
                if (!gl) return null;
                
                return {
                    vendor: gl.getParameter(gl.VENDOR),
                    renderer: gl.getParameter(gl.RENDERER),
                    version: gl.getParameter(gl.VERSION)
                };
            }

            function verifyHuman() {
                let mouseMoves = 0;
                let keyPresses = 0;
                let scrolls = 0;
                
                document.addEventListener('mousemove', () => {
                    mouseMoves++;
                    checkBehavior();
                });
                
                document.addEventListener('keypress', () => {
                    keyPresses++;
                    checkBehavior();
                });
                
                document.addEventListener('scroll', () => {
                    scrolls++;
                    checkBehavior();
                });
                
                function checkBehavior() {
                    if (mouseMoves > 3 || keyPresses > 0 || scrolls > 0) {
                        submitVerification();
                    }
                }
                
                // Fallback timeout
                setTimeout(submitVerification, 3000);
            }

            function submitVerification() {
                if (window.verified) return;
                window.verified = true;

                const data = {
                    browser: getBrowserData(),
                    canvas: getCanvasFingerprint(),
                    webgl: getWebGLFingerprint(),
                    timestamp: Date.now()
                };

                fetch('verify_challenge.php?token=<?php echo urlencode($token); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.href = result.redirect;
                    } else {
                        document.body.innerHTML = '<div class="loader"><p>Verification failed. Please try again.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Verification error:', error);
                });
            }

            // Start verification
            verifyHuman();
        })();
    </script>
</body>
</html>
