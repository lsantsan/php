<?php

abstract class API {

    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';

    /**
     * Property: resource
     * The Model requested in the URI. eg: /files
     */
    protected $resource = '';

    /**
     * Property: subResource
     * An optional additional descriptor about the resource, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    //protected $subResource = '';

    /**
     * Property: args
     * Any additional URI components after the resource and subResource have been removed, in our
     * case, an integer ID for the resource. eg: /<resource>/<subResource>/<arg0>/<arg1>
     * or /<resource>/<arg0>
     */
    protected $url = Array();

    /**
     * Property: file
     * Stores the input of the PUT request
     */
    protected $file = Null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
        /* header("Access-Control-Allow-Orgin: *");
          header("Access-Control-Allow-Methods: *");
         */
        header("Content-Type: application/json");

        $this->url = explode('/', rtrim($request, '/'));
        $this->resource = array_shift($this->url);
       /* if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->subResource = array_shift($this->args);
        }*/

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        $rawData = file_get_contents("php://input"); //Enables this service to receive JSON inputs
        switch ($this->method) {
            case 'DELETE':
            case 'POST':                
                $this->payload = $this->_cleanInputs($rawData);                                
                break;
            case 'GET':
                $this->payload = $this->_cleanInputs($_GET);
                break;
            case 'PUT':
                $this->payload = $this->_cleanInputs($rawData);
                //$this->file = $rawData;
                break;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    public function processAPI() {
        if (method_exists($this, $this->resource)) {
            return $this->_response($this->{$this->resource}());
        }
        return $this->_response("No Resource: $this->resource", 404);
    }

    protected function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

}
