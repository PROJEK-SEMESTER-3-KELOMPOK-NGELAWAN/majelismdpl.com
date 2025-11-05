<?php
/**
 * Midtrans Configuration Helper
 * Handles APP_MODE detection dan Midtrans SSL configuration
 */

class MidtransConfig
{
    private static $appMode = null;

    /**
     * Detect application mode
     * Priority: APP_MODE constant > HTTP_HOST detection > LOCAL (default)
     */
    public static function detectAppMode()
    {
        if (self::$appMode !== null) {
            return self::$appMode;
        }

        // 1. Check if APP_MODE is defined in config.php
        if (defined('APP_MODE') && !empty(APP_MODE)) {
            self::$appMode = APP_MODE;
            return self::$appMode;
        }

        // 2. Auto-detect from HTTP_HOST
        if (isset($_SERVER['HTTP_HOST'])) {
            $httpHost = $_SERVER['HTTP_HOST'];
            
            if (stripos($httpHost, 'ngrok') !== false) {
                self::$appMode = 'NGROK';
            } elseif (stripos($httpHost, 'majelismdpl.com') !== false) {
                self::$appMode = 'PRODUCTION';
            } else {
                self::$appMode = 'LOCAL';
            }
        } else {
            // 3. Default to LOCAL if no HTTP_HOST
            self::$appMode = 'LOCAL';
        }

        return self::$appMode;
    }

    /**
     * Configure Midtrans based on app mode
     */
    public static function configure()
    {
        try {
            if (!class_exists('\Midtrans\Config')) {
                throw new Exception('Midtrans library not found');
            }

            // Set basic Midtrans config
            \Midtrans\Config::$serverKey = 'Mid-server-4bU4xow9Vq2yH-1WicaeTMiq';
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Detect app mode
            $appMode = self::detectAppMode();

            // Set SSL based on app mode
            if ($appMode === 'LOCAL') {
                // Local: disable SSL verification
                \Midtrans\Config::$curlOptions = array(
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_TIMEOUT => 30
                );
            } else {
                // NGROK & PRODUCTION: enable SSL verification
                \Midtrans\Config::$curlOptions = array(
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_TIMEOUT => 30
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Midtrans Configuration Error: ' . $e->getMessage());
        }
    }

    /**
     * Get current app mode
     */
    public static function getAppMode()
    {
        return self::detectAppMode();
    }
}
?>
