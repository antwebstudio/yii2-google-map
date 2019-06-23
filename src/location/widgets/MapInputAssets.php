<?php
namespace ant\location\widgets;

class MapInputAssets extends \yii\web\AssetBundle {
    public $sourcePath = '@vendor/antweb/yii2-google-map/assets';
	
    public $css = [
	];
	
    public $js = [
        'js/map-input.js',
		YII_DEBUG ? 'https://cdn.jsdelivr.net/npm/vue' : 'https://cdn.jsdelivr.net/npm/vue',
    ];
	
    public $depends = [
        'yii\web\YiiAsset',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view, $googleMapApiKey = null) {
		if (!isset($googleMapApiKey)) throw new \Exception('Google map api key is not set. ');
		
        $view->registerJsFile('https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&sensor=true&key=' . $googleMapApiKey);
        return parent::register($view);
    }
}
