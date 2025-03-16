<?php
namespace Eticsoft\Sanalpospro;

class EticConfig
{
    private static $settings;

    public static function setSettings($settings)
    {
        self::$settings = $settings;
    }

    public static function get($key)
    {
        $getSettings = self::$settings->getSetting('payment_garantibbva');
        if (!isset($getSettings['payment_garantibbva_' . $key])) {
            throw new \Exception('Config key not found: ' . $key);
        }
        return $getSettings['payment_garantibbva_' . $key];
    }

    public static function set($key, $value)
    {
        $getSettings = self::$settings->getSetting('payment_garantibbva');
        if (!isset($getSettings['payment_garantibbva_' . $key])) {
            throw new \Exception('Config key not found: ' . $key);
        }
        $getSettings['payment_garantibbva_' . $key] = $value;
        self::$settings->editSetting('payment_garantibbva', $getSettings);
    }
}