<?php
// read sensor ID ('esp8266-'+ChipID)
$headers = array();
if (isset($_SERVER['HTTP_SENSOR']))
    $headers['Sensor'] = $_SERVER['HTTP_SENSOR'];
if (isset($_SERVER['HTTP_X_SENSOR']))
    $headers['Sensor'] = $_SERVER['HTTP_X_SENSOR'];
// stop if no sensor name is detected
if (! isset($headers['Sensor'])) {
    die("no sensor id sended!");
}
$db = new PDO("sqlite:/usr/local/data/dust.db");
// set the database parameters
//$database_name = "luftdaten";
//$database_host = "127.0.0.1";
//$database_user = "change_it";
//$database_password = "change_it";
//$table_name = "sensor_data";
//$dbFile = '/usr/local/data/dust.db';
// establish the database connection
//$pdo = new PDO('mysql:host=' . $database_host . ';dbname=' . $database_name, $database_user, $database_password);
//$db = new PDO("sqlite:$dbFile");
// read the content sended to the API file
$json = file_get_contents('php://input');

// decode the encoded data into the results array
$results = json_decode($json, true);

// declare possible field names
$possible_fields = array(
    "temperature",
    "humidity",
    "BMP280_temperature",
    "BMP280_pressure",
    "BMP_temperature",
    "BMP_pressure",
    "BME280_temperature",
    "BME280_humidity",
    "BME280_pressure",
    "SDS_P1",
    "SDS_P2",
    "HTU21D_temperature",
    "HTU21D_humidity",
    "SPS30_P0",
    "SPS30_P2",
    "SPS30_P4",
    "SPS30_P1",
    "PMS_P0",
    "PMS_P1",
    "PMS_P2",
    "signal"
);

// copy sensor data values to values array
foreach ($results["sensordatavalues"] as $sensordatavalues) {
    $values[$sensordatavalues["value_type"]] = $sensordatavalues["value"];
}

if(!isset($values["temperature"]) && isset($values["BME280_temperature"])){
	$values["temperature"] = $values["BME280_temperature"];
}
if(!isset($values["temperature"]) && isset($values["BMP280_temperature"])){
	$values["temperature"] = $values["BMP280_temperature"];
}

if(!isset($values["humidity"]) && isset($values["BME280_humidity"])){
	$values["humidity"] = $values["BME280_humidity"];
}
   
// set missing fields to ensure their presence
foreach ($possible_fields as $possible_field) {
    if (! isset($values[$possible_field])) {
        $values[$possible_field] = NULL;
    }
}
// prepare the database query
$qry = $db->prepare("REPLACE INTO sensors  (Currentdate, Currentime, SensorID, Temp, Humidity, SDS_P1, SDS_P2, BMP280_temperature, BMP280_pressure, BME280_temperature, BME280_humidity, BME280_pressure, BMP_temperature, BMP_pressure, HTU21D_temperature, HTU21D_humidity, SPS30_P0, SPS30_P2, SPS30_P4, SPS30_P1, PMS_P0, PMS_P1, PMS_P2, Signal) VALUES (Date('now'), time('now', 'localtime'),:SensorID,:temperature,:humidity,:SDS_P1,:SDS_P2,:BMP280_temperature,:BMP280_pressure,:BME280_temperature,:BME280_humidity,:BME280_pressure,:BMP_temperature,:BMP_pressure,:HTU21D_temperature,:HTU21D_humidity,:SPS30_P0,:SPS30_P2,:SPS30_P4,:SPS30_P1,:PMS_P0,:PMS_P1,:PMS_P2,:Signal)"); 
// execute the database query
$qry->execute(array(
    ':SensorID' => $headers['Sensor'],
    ':Temp' => $values["temperature"],
    ':Humidity' => $values["humidity"],
    ':SDS_P1' => $values["SDS_P1"],
    ':SDS_P2' => $values["SDS_P2"],
    ':BMP280_temperature' => $values["BMP280_temperature"],
    ':BMP280_pressure' => $values["BMP280_pressure"],
    ':BME280_temperature' => $values["BME280_temperature"],
    ':BME280_humidity' => $values["BME280_humidity"],
    ':BME280_pressure' => $values["BME280_pressure"],
    ':BMP_temperature' => $values["BMP_temperature"],
    ':BMP_pressure' => $values["BMP_pressure"],
    ':HTU21D_temperature' => $values["HTU21D_temperature"],
    ':HTU21D_humidity' => $values["HTU21D_humidity"],
    ':SPS30_P0' => $values["SPS30_P0"],
    ':SPS30_P2' => $values["SPS30_P2"],
    ':SPS30_P4' => $values["SPS30_P4"],
    ':SPS30_P1' => $values["SPS30_P1"],
    ':PMS_P0' => $values["PMS_P0"],
    ':PMS_P1' => $values["PMS_P1"],
    ':PMS_P2' => $values["PMS_P2"],
    ':Signal' => $values["signal"]
));
