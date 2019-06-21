<?php
namespace ant\location\widgets;

class MapInput extends \yii\widgets\InputWidget {
	public function run() {
        parent::run();
		
		MapInputAssets::register($this->view);
	}
}