<?php
namespace ant\location\widgets;

class MapInput extends \yii\widgets\InputWidget {
	public $form;
	public $googleMapApiKey;
	public $countryList = [];
	public $singleAttributeMode = false;
	public $fields = ['venueName', 'address_1', 'address_2', 'city', 'postcode', 'countryIso2', 'custom_state', 'latitude', 'longitude'];
	
	public function init() {
		if (!isset($this->googleMapApiKey)) throw new \Exception('Google map api key is not set. ');
		
	}
	
	public function run() {
        parent::run();
		
		MapInputAssets::register($this->view, $this->googleMapApiKey);
		
		return $this->render('map-input', [
			'form' => isset($this->field->form) ? $this->field->form : $this->form,
			'model' => $this->model,
			'attribute' => $this->attribute,
		]);
	}
	
	public function getFieldsVisibility() {
		$fields = [];
		foreach ($this->fields as $name) {
			$fields[$name] = true;
		}
		return $fields;
	}
	
	public function isShow($attribute) {
		return true;
		return in_array($attribute, $this->fields);
	}
}