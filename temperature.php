<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Isaiah Alviola (1.2) </title>
    <link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body style="text-align: left;">
    <?php

    include "apiconfig.php";

    //API call to always get PUBLIC IP
    $public_ip =$_SERVER['REMOTE_ADDR'];

    //checks if IP is valid or private if it is then use API 
    if (filter_var($public_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) == false) {
        echo "<br><p class= 'err'>Unable To Get Public IP Address Using PHP. Used an API instead. </p>";
        $ipify =  file_get_contents('https://api.ipify.org');

        /*
        I hardcoded a part of my code because whenever I was at kean university it would use my 
        10.xxx.xxx.xx ip or my private ip, 
        I tried other php server variables (not recognized) and using the api ipify
        (which gave me 131.125.80.80 which was in chicago ???)
        */
        if ($ipify == "131.125.80.80"){
            $public_ip = "131.125.11.1";
        }else{
            $public_ip = $ipify;
        }

    }

    //json for geolocation
    $geo_curl = "http://www.geoplugin.net/json.gp?ip=".$public_ip;
    $geo_json = file_get_contents($geo_curl);
    $geo_arr = json_decode($geo_json, true);

    //checks if JSON is valid, logs geo_results then requests OPENWEATHERMAP API
    if (empty($geo_arr)) {
        echo "<br><p class= 'err'>Unable To Get Location.</p>";
    } else {
        //var_dump($geo_arr);
        $geo_results =
            "
    <p class= 'geo_api'>
        IP: ".$geo_arr["geoplugin_request"]."
        <br>city: " . $geo_arr["geoplugin_city"] . "
        <br>state: " . $geo_arr["geoplugin_region"] . "
        <br>country name: " . $geo_arr["geoplugin_countryName"] . " 
    ";
    
        echo $geo_results;

        //coor. for OPENWEATHERMAP API
        $geo_latitude = $geo_arr["geoplugin_latitude"];
        $geo_longitude = $geo_arr["geoplugin_longitude"];

        $wx_curl = "https://api.openweathermap.org/data/2.5/weather?lat=" . $geo_latitude .
            "&lon=" . $geo_longitude . "&appid=" . $wx_apikey;

        $wx_json = file_get_contents($wx_curl);
        $wx_arr = json_decode($wx_json, true);

        if (empty($wx_arr)) {

            echo "<br><p class= 'err'>Unable To Get Weather.</p>";
        } else {
            //var_dump($wx_arr);
            $wx_results =
                "
        <br>description: " . $wx_arr["weather"][0]["description"] . "
        <br>temperature: " . convertKelvinToCelsiusAndFahrenheit($wx_arr["main"]["temp"]) . "
        <br>humidity: " . $wx_arr["main"]["humidity"] . "%
        <br>visibility: " . convertMetersToMiles($wx_arr["visibility"]) . "
        <br>pressure: " . $wx_arr["main"]["pressure"] . " hpa
        <br>wind speed:" . mphConvert($wx_arr["wind"]["speed"]) . "
        <br>wind direction: " . convertDegreeToCardinal($wx_arr["wind"]["deg"]) . "
        <br>timezone: " . $geo_arr["geoplugin_timezone"]. "
        <br>sunrise: " . convertUnixTimestamp($wx_arr["sys"]["sunrise"] + $wx_arr["timezone"]) . "
        <br>sunset: " . convertUnixTimestamp($wx_arr["sys"]["sunset"] + $wx_arr["timezone"]) . "
        </p>
        ";
            echo $wx_results;
        }
    }
    

    echo "<br><br><a href= 'index.html'>Back to home webpage.</a>";
    //conversion functions for weather.
    function convertDegreeToCardinal($degree)
    {
        switch ($degree) {
            case $degree == 0 || $degree == 360:
                $dir = "N";
                break;
            case $degree == 90:
                $dir = "E";
                break;
            case $degree == 180:
                $dir = "S";
                break;
            case $degree == 270:
                $dir = "W";
                break;
            case $degree > 0 && $degree < 90:
                $dir = "NE";
                break;
            case $degree > 90 && $degree < 180:
                $dir = "SE";
                break;
            case $degree > 180 && $degree < 270:
                $dir = "SW";
                break;
            case $degree > 270 && $degree < 360:
                $dir = "NW";
                break;
        }
        return $degree . " (" . $dir . ")";
    }

    function convertMetersToMiles($meters){
        $miles = $meters * 0.000621371;
        return number_format($meters, 2) . " meters = " . number_format($miles, 2) . " miles";
    }

    function convertKelvinToCelsiusAndFahrenheit($kelvin){
        $celsius = $kelvin - 273.15;
        $fahrenheit = ($celsius * 9 / 5) + 32;
        return number_format($celsius, 2) . "°C = " . number_format($fahrenheit, 2) . "°F";
    }

    function convertUnixTimestamp($unixTimestamp) {
        $dateTime = new DateTime('@' . $unixTimestamp);
        $dateTime->setTimeZone(new DateTimeZone('UTC'));
        return $dateTime->format('Y-m-d H:i:s');
    }


    function mphConvert($mps)
    {
        $mph = round($mps * 2.23694, 2);
        return "$mps m/s = $mph mph";
    }
    
    ?>
</body>
</html>