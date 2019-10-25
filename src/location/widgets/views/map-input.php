<?php
use yii\helpers\ArrayHelper;

$enableCustomStateCountryIso = true;
?>
<div id="<?= $this->context->id ?>">
	<div style="margin-bottom: 10px;">
		<a class="btn-default btn" @click="onSwitchButtonClicked" style="cursor: pointer;"><i class="fa fa-search"></i> {{ switchButtonLabel }}</a>
	</div>
	
	<input v-show="isAutoCompleteShow" class="form-control" ref="autocompleteInput" type="text" />
	
	<div v-show="isFormShow" ref="addressForm">
		<?= $form->field($model, 'venue')->textInput(['v-model' => 'venueName']) ?>
		
		<?= $form->field($model, 'address_1')->textInput(['v-model' => 'address.address1']) ?>

		<?= $form->field($model, 'address_2')->textInput(['v-model' => 'address.address2']) ?>
		
		<div class="row">
			<div class="col-md-6">
				<?= $form->field($model, 'city')->textInput(['v-model' => 'address.city']) ?>
			</div>
			<div class="col-md-6">
				<?= $form->field($model, 'postcode')->textInput(['v-model' => 'address.postal_code']) ?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<?= $form->field($model, 'countryIso2')->dropdownList(
					ArrayHelper::map($this->context->countryList, 'iso_code_2', 'name'), 
					[
						'options' => [
							isset($model->country)? $model->country->iso_code_2 : '' => 
							['Selected' => true ],
						],
						'prompt' => 'Select ...',
						'v-model' => 'address.country',
					]) 
				?>
			</div>

			<div class="col-md-6">
				<?= $form->field($model, 'custom_state')->textInput(['v-model' => 'address.state']) ?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<?= $form->field($model, 'latitude')->textInput(['ref' => 'latitude', 'v-model' => 'coordinate.latitude']) ?>
			</div>
			<div class="col-md-6">
				<?= $form->field($model, 'longitude')->textInput(['ref' => 'longitude', 'v-model' => 'coordinate.longitude']) ?>
			</div>
		</div>
	</div>
	
	<div v-show="isMapShow" ref="map" style="width: 100%; height: 500px;"></div>
</div>

<?php \ant\widgets\JsBlock::begin() ?>
<script>
var elementId = '#<?= $this->context->id ?>';
var app = new Vue({
	el: elementId,
	data() {
		var $el = document.querySelector(elementId);
		
		return {
			map: null,
			marker: null,
			draggable: true,
			hideMarker: false,
			isMapShow: false,
			isAutoCompleteShow: true,
			isFormShow: false,
			place: {},
			address: {
				address1: $el.querySelector('[v-model="address.address1"]').value,
				address2: $el.querySelector('[v-model="address.address2"]').value,
				state: $el.querySelector('[v-model="address.state"]').value,
				city: $el.querySelector('[v-model="address.city"]').value,
				postal_code: $el.querySelector('[v-model="address.postal_code"]').value,
				country: $el.querySelector('[v-model="address.country"]').value,
			},
			venueName: $el.querySelector('[v-model="venueName"]').value, // venueName should be separated from address, as it won't be updated when address is updated
			coordinate: {
				latitude: $el.querySelector('[v-model="coordinate.latitude"]').value,
				longitude: $el.querySelector('[v-model="coordinate.longitude"]').value,
			},
			initialized: false
		};
	},
	methods: {
		initValueFromHtmlInline() {
	
			this.venueName = this.$el.querySelector('[v-model="venueName"]').value;
			this.address = {
				address1: this.$el.querySelector('[v-model="address.address1"]').value,
				address2: this.$el.querySelector('[v-model="address.address2"]').value,
				state: this.$el.querySelector('[v-model="address.state"]').value,
				city: this.$el.querySelector('[v-model="address.city"]').value,
				postal_code: this.$el.querySelector('[v-model="address.postal_code"]').value,
				country: this.$el.querySelector('[v-model="address.country"]').value,
			}
			
			this.coordinate = {
				latitude: this.$el.querySelector('[v-model="coordinate.latitude"]').value,
				longitude: this.$el.querySelector('[v-model="coordinate.longitude"]').value,
			}
			
		},
		initAutoComplete(container) {
			var self = this;
			var autocomplete = new google.maps.places.Autocomplete(container);

			google.maps.event.addListener(autocomplete, 'place_changed', function() {
				var place = autocomplete.getPlace();
				
				if (!place) return;
				
				self.setLocation(place);
				self.setVenueName(self.addressComponent.venue);
				self.isFormShow = true;
				self.isAutoCompleteShow = false;
			});
			
			// Prevent enter when select autocomplete which will submit the form
			google.maps.event.addDomListener(container, 'keydown', function(event) { 
				if (event.keyCode === 13) { 
					event.preventDefault(); 
				}
			}); 
		},
		setAddress(address) {
			this.address = address;
		},
		setVenueName(venueName) {
			this.venueName = venueName;
		},
		setLocation(place) {
			this.place = place;
			this.setAddress(this.addressComponent);
			
			if (!this.isMapShow) this.showMap();
			
			if (place.geometry.location) {
				this.coordinate = {
					latitude: place.geometry.location.lat(),
					longitude: place.geometry.location.lng(),
				}
			}
		},
		setMarkerCoordinate(latitude, longitude) {
			var latlng = new google.maps.LatLng(latitude, longitude);
			this.setMarker(latlng);
			this.map.setCenter(latlng);
		},
		setMarker(latLng) {
			var self = this;
			
			if (this.marker) {
				this.marker.remove();
			}
			if (this.hideMarker) {
				return;
			}
			this.marker = new google.maps.Marker({
				'position' : latLng,
				'map' : this.map,
				'draggable' : this.draggable
			});

			if (this.draggable) {
				google.maps.event.addListener(this.marker, 'dragend', function() {
					self.marker.changePosition(self.marker.getPosition());
				});
			}

			this.marker.remove = function() {
				google.maps.event.clearInstanceListeners(this);
				this.setMap(null);
			};

			this.marker.changePosition = this.onMarkerMoved;
		},
		onSwitchButtonClicked() {
			this.$refs.autocompleteInput.value = '';
			this.isMapShow = !this.isMapShow;
			this.isFormShow = !this.isFormShow;
			this.isAutoCompleteShow = !this.isAutoCompleteShow;
			
			this.$refs.autocompleteInput.focus();
		},
		onMarkerMoved(latLng) {
			this.coordinate = {
				latitude: latLng.lat(),
				longitude: latLng.lng(),
			};
		},
		geocode(latitude, longitude, callback) {
			var self = this;
			var geocoder = new google.maps.Geocoder();
			
			geocoder.geocode({latLng: new google.maps.LatLng(latitude, longitude)}, function(results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						self.place = results[0];
						
						callback(this.place);
					}
					return false;
				}
			);
		},
		showAddressForm() {
			this.isAutoCompleteShow = false;
			this.isFormShow = true;
			this.showMap();
		},
		hideAddressForm() {
			this.isAutoCompleteShow = true;
			this.isFormShow = false;
			this.isMapShow = false;
		},
		showMap() {
			var self = this;
			
			var listener = google.maps.event.addListener(this.map, "idle", function() { 
				self.map.setZoom(12); 
				google.maps.event.removeListener(listener); 
			});

			this.isMapShow = true;
			
			//google.maps.event.trigger(this.map, 'resize');
			
		},
	},
	beforeMount() {
		//this.initValueFromHtmlInline();
	},
	mounted() {
		
		var mapOptions = {
			center: new google.maps.LatLng(this.$refs.latitude.value, this.$refs.longitude.value),
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			panControl: true
		};
		
		this.map = new google.maps.Map(this.$refs.map, mapOptions);
		this.setMarker(new google.maps.LatLng(this.$refs.latitude.value, this.$refs.longitude.value));
		this.initAutoComplete(this.$refs.autocompleteInput);
		
		if (this.draggable) {
			google.maps.event.addListener(this.map, 'click', function(e) {
				this.onMarkerMoved(e.latLng);
				self.setMarker(e.latLng);
			});
		}
		
		// Show address form if contain error or if the address is already filled in.
		var element = this.$refs.addressForm.querySelector('.has-error');
		if ((element && element.classList.contains('has-error')) || this.isAlreadySearch) {
			this.showAddressForm();
		}
		this.initialized = true;
	},
	watch: {
		'coordinate.latitude': function() {
			var self = this;
			
			if (this.initialized && this.coordinate.latitude && this.coordinate.longitude) {
				this.setMarkerCoordinate(this.coordinate.latitude, this.coordinate.longitude);
				this.geocode(this.coordinate.latitude, this.coordinate.longitude, function(place) {
					self.setAddress(self.addressComponent);
				});
			}
		},
		'coordinate.longitude': function() {
			var self = this;
			
			if (this.initialized && this.coordinate.latitude && this.coordinate.longitude) {
				this.setMarkerCoordinate(this.coordinate.latitude, this.coordinate.longitude);
				this.geocode(this.coordinate.latitude, this.coordinate.longitude, function(place) {
					self.setAddress(self.addressComponent);
				});
			}
		},
	},
	computed: {
		isAlreadySearch() {
			return this.place.geometry || (this.coordinate.latitude && this.coordinate.longitude);
		},
		switchButtonLabel() {
			if (this.isFormShow) {
				return this.isAlreadySearch ? 'Search another location' : 'Search location';
			} else {
				return 'Enter addess';
			}
		},
		addressComponent() {
			var address = {
				address1: (this.rawAddress.long.street_number ? this.rawAddress.long.street_number : '') 
					+ (this.rawAddress.long.street_number && this.rawAddress.long.route ? ' ' : '')
					+ (this.rawAddress.long.route ? this.rawAddress.long.route : ''),
				address2: '',
				state: this.rawAddress.long.administrative_area_level_1,
				city: this.rawAddress.long.locality,
				postal_code: this.rawAddress.long.postal_code,
				country: this.rawAddress.short.country,
			}
			
			if (this.place.name) {
				address.venue = this.place.name;
			} else if (address.address1) {
				address.venue = address.address1;
			}
			
			return address;
		},
		rawAddress() {
			var computed = {long: {} , short: {}};
			
			for (var i in this.place.address_components) {
				var address = this.place.address_components[i];
				
				for (var n in address.types) {
					computed['long'][address.types[n]] = address.long_name;
					computed['short'][address.types[n]] = address.short_name;
				}
			}
			return computed;
		}
	}
})
</script>
<?php \ant\widgets\JsBlock::end() ?>