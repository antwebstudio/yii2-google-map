<?php
namespace ant\location\widgets;

class MapInput extends \yii\widgets\InputWidget {
	public $form;
	public $googleMapApiKey;
	public $countryList = [];
	
	public function init() {
		if (!isset($this->googleMapApiKey)) throw new \Exception('Google map api key is not set. ');
		
	}
	
	public function run() {
        parent::run();
		
		MapInputAssets::register($this->view, $this->googleMapApiKey);
		
		return $this->render('map-input', [
			'form' => isset($this->field->form) ? $this->field->form : $this->form,
			'model' => $this->model,
		]);
	}
}