<?php
	class Ceca{

		// Campos obligatorios.
		public $Clave_encriptacion;
		public $MerchantID;
		public $AcquirerBIN;
		public $TerminalID;
		public $Num_operacion;
		public $Importe;
		public $TipoMoneda = '978';
		public $Exponente = 2;
		public $URL_OK;
		public $URL_NOK;
		public $Firma;
		public $Cifrado = 'SHA2';
		public $Pago_soportado = 'SSL';

		// Campos opcionales.
		public $Idioma = 1;
		public $Descripcion;
		public $Pago_elegido = 'SSL'; // Debe de ser SSL si la solicitud es Comercio
		public $PAN;
		public $Caducidad;
		public $CVV2;
		public $Referencia;

		// Url de entorno de pruebas (http://tpv.ceca.es:8000/cgi-bin/tpv) o real (https://pgw.ceca.es/cgi-bin/tpv)
		public $environmentURL = 'http://tpv.ceca.es:8000/cgi-bin/tpv'; 

		// Solicitud de datos (TPV o Comercio)
		public $solicitud = 'TPV';

		// Bootstrap
		public $bootstrap = FALSE;

		private $campos_obligatorios = array('MerchantID', 'AcquirerBIN', 'TerminalID', 'Num_operacion', 'Importe', 'TipoMoneda', 'Exponente', 'URL_OK', 'URL_NOK', 'Firma', 'Cifrado', 'Pago_soportado');
		private $campos_opcionales = array('Idioma', 'Descripcion', 'Referencia');
		private $campos_hidden = array('MerchantID', 'AcquirerBIN', 'TerminalID', 'Num_operacion', 'Importe', 'TipoMoneda', 'Exponente', 'URL_OK', 'URL_NOK', 'Firma', 'Cifrado', 'Pago_soportado', 'Idioma', 'Descripcion', 'Pago_elegido', 'Referencia');
		private $campos_comercio = array('PAN', 'Caducidad', 'CVV2', 'Pago_elegido');

		/**
		 * Default construct
		 *
		 * @param      array  $config  The configuration
		 */
		public function __construct(array $config){
			foreach ($config as $key => $value) {
				$this->{$key} = $value;
			}
		}

		/**
		 * Creates the form to pay, if $solicitud == FALSE the form will only contains hidden inputs, else, it will appends text inputs to get the client's data.
		 *
		 * @return     string  ( the form )
		 */
		public function createForm(){
			if(!isset($this->environmentURL))
				exit('El entorno no está definido');

			$form = "<form action=\"$this->environmentURL\" method=\"POST\">".PHP_EOL;
			foreach ($this->campos_obligatorios as $key => $value) {
				if(isset($this->{$value}) && $this->{$value} !== NULL)
					$form .= '<input '.($this->bootstrap ? 'class="form-control"' : '').' type="'.(in_array($value, $this->campos_hidden) ? 'hidden' : 'text').'" value="'.(in_array($value, $this->campos_hidden) ? $this->{$value} : '').'" name="'.$value.'"/>'.PHP_EOL;
				else
					exit("La variable: $value es obligatoria.");
			}
			if($this->solicitud == 'Comercio'){
				foreach ($this->campos_comercio as $key => $value) {
					if($this->bootstrap)
						$form .= '<div class="form-group>';
					if(!in_array($value, $this->campos_hidden))
						$form .= '<label '.($this->bootstrap ? 'class="control-label"' : '').' for="'.$value.'">'.$value.'</label>';
					$form .= '<input '.($this->bootstrap ? 'class="form-control"' : '').' type="'.(in_array($value, $this->campos_hidden) ? 'hidden' : 'text').'" value="'.(in_array($value, $this->campos_hidden) ? $this->{$value} : '').'" name="'.$value.'"/>'.PHP_EOL;
					if($this->bootstrap)
						$form .= '</div>';
				}
			}
			foreach ($this->campos_opcionales as $key => $value) {
				if(isset($this->{$value}) && $this->{$value} !== NULL)
					$form .= '<input '.($this->bootstrap ? 'class="form-control"' : '').' type="'.(in_array($value, $this->campos_hidden) ? 'hidden' : 'text').'" value="'.(in_array($value, $this->campos_hidden) ? $this->{$value} : '').'" name="'.$value.'"/>'.PHP_EOL;
			}

			$form .= '<input type="submit" '.($this->bootstrap ? 'class="btn btn-success"' : '').' value="'.($this->solicitud == 'Comercio' ? 'Aceptar' : 'Pagar').'"></form>';
			return $form;
		}

		public function createFirma(){
			if($this->Clave_encriptacion == NULL)
				exit('No está establecida la clave de encriptación');

			if(in_array(NULL, array($this->MerchantID, $this->AcquirerBIN, $this->TerminalID, $this->Num_operacion, $this->Importe, $this->TipoMoneda, $this->Exponente, $this->URL_OK, $this->URL_NOK)))
				exit('Hay valores obligatorios que están vacíos');

			$this->Firma = hash('sha256', $this->Clave_encriptacion.$this->MerchantID.$this->AcquirerBIN.$this->TerminalID.$this->Num_operacion.$this->Importe.$this->TipoMoneda.$this->Exponente.'SHA2'.$this->URL_OK.$this->URL_NOK);
		}
	}
?>