<?php
namespace ant\location\widgets;

class MapInputAssets extends \yii\web\AssetBundle {
    public $sourcePath = '@vendor/antweb/yii2-google-map/assets';
	
    public $css = [
	];
	
    public $js = [
        'js/map-input.js',
    ];
	
    public $depends = [
        'yii\web\YiiAsset',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view, $googleMapApiKey) {
        $view->registerJsFile('https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&sensor=true&key=' . $googleMapApiKey);
        return parent::register($view);
    }
}
