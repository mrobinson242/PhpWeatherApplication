<?php

    // Latitude/Longitude
    $lat;
    $long;

    // City / Street / State
    $city;
    $street;
    $state;

    // Timezone / Temp / Summary
    $timezone;
    $temperature;
    $summary;

    $dataArray;
    $resultArray;
    
    // Page2
    $detailSummary;
    $detailTemp;
    $detailIcon;
    $precipIntensity;
    $precipProbability;
    $detailWindSpeed;
    $detailHumidity;
    $detailVisibility;
    $detailTimezone;
    $sunriseTime;
    $sunsetTime;

    // Current Location Box
    $locBox;
    $xml;

    /**
     * displaySummary
     */
    function displaySummary($detailArray)
    {
        // Update Forecast
        $GLOBALS['detailSummary'] = $detailArray['currently']['summary'];
        $GLOBALS['detailTemp'] = round($detailArray['currently']['temperature']);
        $GLOBALS['detailIcon'] = $detailArray['currently']['icon'];
       
        // Update Details
        $GLOBALS['precipIntensity'] = $detailArray['currently']['precipIntensity'];
        $GLOBALS['precipProbability'] = round($detailArray['currently']['precipProbability'] * 100);
        $GLOBALS['detailWindSpeed'] = $detailArray['currently']['windSpeed'];
        $GLOBALS['detailHumidity'] = round($detailArray['currently']['humidity'] * 100);
        $GLOBALS['detailVisibility'] = $detailArray['currently']['visibility'];
        $GLOBALS['detailTimezone'] = $detailArray['timezone'];
        $GLOBALS['sunriseTime'] = $detailArray['daily']['data']['0']['sunriseTime'];
        $GLOBALS['sunsetTime'] = $detailArray['daily']['data']['0']['sunsetTime'];
    }

    /**
     * displayForecast
     */
    function displayForecast($street, $city, $state, $array, $lat, $lon)
    {
        // Update Card View
        $GLOBALS['city'] = $city;
        $GLOBALS['street'] = $street;
        $GLOBALS['state'] = $state;

        $GLOBALS['timezone'] = $array['timezone'];
        $GLOBALS['temperature'] = round($array['currently']['temperature']);
        $GLOBALS['summary'] = $array['currently']['summary'];

        // Update Card Values
        $GLOBALS['resultArray'] = $array;

        // Update Data Array for Table
        $GLOBALS['dataArray'] = $array['daily']['data'];
    }

    /**
     * resetVars
     */
    function resetVars()
    {
        // Clear City/Street/State
        unset($GLOBALS['city']);
        unset($GLOBALS['street']);
        unset($GLOBALS['state']);
        
        // Clear Timezone/Temp/Summary
        unset($GLOBALS['timezone']);
        unset($GLOBALS['temperature']);
        unset($GLOBALS['summary']);
        
        // Clear Result/Data Array
        unset($GLOBALS['resultArray']);
        unset($GLOBALS['resultArray']);
        
        // Clear Lat/Long
        unset($GLOBALS['lat']);
        unset($GLOBALS['lon']);
        
        // Clear Google File
        unset($GLOBALS['xml']);
    }

    // Check if Search has been selected
    if(isset($_POST["search"]))
    {
        // Reset Variables
        resetVars();

        if(isset($_POST["lat"]) && isset($_POST["lon"]) && !empty($_POST["lat"]) && !empty($_POST["lon"]))
        {         
            // Get Variables
            $GLOBALS['lat'] = $_POST["lat"];
            $GLOBALS['lon'] = $_POST["lon"];
            $city = $_POST["hiddenCity"];

            // Formulate Forecast.io API Query URL
            $forecastUrl = "https://api.forecast.io/forecast/"."8cbc99f201f8a314fe08f86dc72bebf2"."/".$lat.",".$lon."/?exclude=minutely,hourly,alerts,flags";

            // Get Forecast Contents from Query
            $forecastFile = file_get_contents($forecastUrl);

            // Convert to array
            $array = json_decode($forecastFile, true);

            // Update Current Location Checkbox
            $GLOBALS['locBox'] = $_POST["locBox"];

            // Display Forecast
            displayForecast($street, $city, $state, $array, $lat, $lon);
        }
        else
        {
            // Check if Street/City/State have been set
            if(isset($_POST["street"]) && isset($_POST["city"]) && isset($_POST["state"]) &&
               !empty($_POST["street"]) && !empty($_POST["city"]) && !empty($_POST["state"]))
            {
                // Set Input Variables
                $GLOBALS['street'] = $_POST["street"];
                $GLOBALS['city'] = $_POST["city"];
                $GLOBALS['state'] = $_POST["state"];
                
                // Formulate Google API Query URL
                $url = "https://maps.googleapis.com/maps/api/geocode/xml?address=".$street.",".$city.",".$state."&key="."AIzaSyBjG29Evn-mX9C6e-Ungqvy8qa_BrXqd2w";

                // Set Contents
                $googleFile = file_get_contents($url);

                if($googleFile)
                {
                    if(simplexml_load_string($googleFile))
                    {
                        // Get XML
                        $xml = simplexml_load_string($googleFile);

                        // Check Status
                        if($xml->status == "OK")
                        {
                            // Update xml Global
                            $GLOBALS['xml'] = $xml;

                            // Get Lat/Long
                            $GLOBALS['lat'] = $xml->result->geometry->location->lat;
                            $GLOBALS['lon'] = $xml->result->geometry->location->lng;

                            // Formulate Forecast.io API Query URL
                            $forecastUrl = "https://api.forecast.io/forecast/"."8cbc99f201f8a314fe08f86dc72bebf2"."/".$lat.",".$lon."/?exclude=minutely,hourly,alerts,flags";

                            // Get Forecast Contents from Query
                            $forecastFile = file_get_contents($forecastUrl);

                            // Convert to array
                            $array = json_decode($forecastFile, true);

                            // Display Forecast
                            displayForecast($street, $city, $state, $array, $lat, $lon);
                        }
                        else
                        {
                            unset($GLOBALS['xml']);
                        }
                    }
                    else
                    {
                        unset($GLOBALS['xml']);
                    }
                }
                else
                {
                    unset($GLOBALS['xml']);
                }
            }
        }
    }
    else
    {
        // Check that Lat/Long are not Null
        if(isset($_GET["lat"]) && isset($_GET["lon"]) && isset($_GET["time"]))
        {
            // Get Variables
            $lat = $_GET["lat"];
            $lon = $_GET["lon"];
            $epoch = $_GET["time"];

            // Check if Location Box passed
            if(isset($_GET['locBox']))
            {
                // Update Location Box
                $GLOBALS['locBox'] = $_GET["locBox"];
            }

            // Formulate Dark Sky API Query URL
            $summaryUrl = "https://api.darksky.net/forecast/"."8cbc99f201f8a314fe08f86dc72bebf2"."/".$lat.",".$lon.",".$epoch."?exclude=minutely";

            // Get Detail Contents from Query
            $detailFile = file_get_contents($summaryUrl);

            // Convert to array
            $detailArray = json_decode($detailFile, true);

            // Display Summary Details
            displaySummary($detailArray);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>HW6</title>
        <!-- Internal Style -->
        <style>
            /** Body of Document */
            body
            {
                /** Alignment */
                margin: 0px;
                padding: 0px;
            }

            /** Document Header */
            h1
            {
                /** Alignment */
                text-align: center;
                
                /** Font Style */
                color: white;
                font-size: 45px;
                font-style: italic;
            }
            
            .mainSection
            {
                margin-top: -25px;
            }
            
            /** CSS for Data Entry Labels */
            .dataEntryLabel
            {
                /** Width */
                width: 55px;
                
                /** Font Style */
                color: white;
                font-size: 20px;
                font-weight: bold;
                
                /** Alignment */
                display: inline-block;
                text-align: left;
            }
            
            /** CSS for Dropdown Label */
            .dropdownLabel
            {
                /** Font Style */
                color: white;
                font-size: 20px;
                font-weight: bold;
                
                /** Alignment */
                display: inline-block;
                text-align: left;
            }
            
            /** CSS for Data Entry Section */
            .dataEntrySection
            {    
                /** Alignment */
                margin-left: 80px;
                float: left;

                /** Width/Height */
                width: 464px;
                height: 150px;
                
                /** Formatting */
                line-height: 25px;
            }
            
            /** CSS for Line Section */
            .lineSection
            {              
                /** Width/Height */
                width: 6px;
                height: 150px;
                
                /** Alignment */
                float: left
            }
            
            /** CSS for CheckBox Section */
            .checkBoxSection
            {
                /** Width/Height */
                width: 280px;
                height: 150px;
                
                /** Alignment */
                float: right;
            }
            
            /** CSS for Check Location Label */
            .currentLocationBox
            {
                /** Font Style */
                color: white;
                font-size: 20px;
                font-weight: bold;
            }

            /** Main Page Div */
            .page
            {
                /** Green Haze Color */
                background-color: #00AF1C;
                
                /** Width / Height */
                width: 1000px;
                height: 300px;
                
                /** Border */
                border-radius: 25px;

                /** Alignment */
                margin: 50px auto;
            }
            
            /** Card View */
            #cardView {
                /** Dodger Blue Color */
                background-color: #26C6FA;

                /** Width / Height */
                width: 550px;
                height: 300px;
                
                /** Border */
                border-radius: 25px;

                /** Alignment */
                margin: 50px auto;
            }
            
            /** Card Labels */
            .cardLabels {
                /** Colors */
                color: white;
                
                /** Width/Height */
                width: 500px;
                height: 230px;

                /** Alignment */
                margin-left: 25px;
                margin-top: 25px;
            }
            
            /** Style of Card Icons Section */
            .cardIcons {
                /** Width / Height */
                width: 500px;
                height: 30px;

                /** Alignment */
                margin-left: 25px;
            }
            
            /** Style of CardView Values Section */
            .cardValues {
                /** Text Color */
                color: white;

                /** Width / Height */
                width: 500px;
                height: 30px;
                
                /** Alignment */
                margin-left: 25px;
                position: absolute;

                /** Font Styling */
                font-weight: bold;
                font-size: 20px;
            }
            
            /** Style of CardView Value */
            .cardValue {
                display: inline-block;
                
                /** Width/Height */
                width:83px;
                height:30px;
                
                text-align: center; 
            }
            
            /** Style of CardView Icons */
            .icon {
                /** Alignment */
                padding-left: 26px;
                padding-right: 27px;
            }
            
            .tooltip {
                position: relative;
                float: left;
            }
            
            /** Style of Icon Tooltip Text */
            .tooltip .iconText {
                visibility: hidden;
                width: 100px;
                color: black;
                background-color: white;
                text-align: center;
                position: absolute;
                z-index: 1;
                margin-left:-50px;
                bottom: 100%;
                left: 50%;
            }
            
            /** Style of hovering over Icon */
            .tooltip:hover .iconText {
                visibility: visible;
            }
            
            .buttonSection
            {
                width: 1000px;
                margin-left: 400px;
                float:left; 
            }
            
            /** Vertical Line */
            .vLine
            {
                border-left: 6px solid white;
                height: 150px;
            }
            
            /** Search Button */
            .searchButton
            {
                /** Color */
                background-color: white;
                
                /** Border */
                border-radius: 5px;
            }
            
            /** Clear Button */
            .clearButton
            {
                /** Color */
                background-color: white;
                
                /** Border */
                border-radius: 5px;
            }
            
            .itemChecked
            {
                position: absolute;
                left: .4rem;
                content: '✓';
                font-weight: 600;
            }
            
            /** Style of City */
            #city {
                /** Font Style */
                font-size: 30px;
                font-weight: bold;
            }
            
            /** Style of Timezone */
            #timezone {
                /** Font Style */
                font-size: 15px;
            }
            
            /** Style of Timezone */
            #temperature {
                /** Font Style */
                font-size: 80px;
                font-weight: bold;
            }
            
            #degreeSymbol {
                
                /** Alignment */
                padding-bottom: 50px;
                margin-left:-15px;
            }
            
            /** Style of Farenheit Label */
            #farenheit {
                /** Font Style */
                font-size: 40px;
                font-weight: bold;
            }
            
            /** Style of Summary */
            #summary {
                /** Font Style */
                font-size: 32px;
                font-weight: bold;
            }  
            
            /** Style of Table View */
            #tableView {

                /** Colors for Table */
                background-color: #95CAF2; /** Jordy Blue */
                color: white;

                /** Border */
                border-width: 2px;
                border-color: #2CA0CC; /** Curious Blue */
                
                /** Width/Height */
                width: 1200px;
                
                /** Alignment */
                margin: 50px auto;
                text-align: center;
                
                /** Font Style */
                font-weight: bold;
            }
            
            .detailView {
                /** Colors */
                background-color: #9DD2DB;    
                color: white;
                
                /** Width / Height */
                width: 600px;
                height: 600px;
                position: relative;
                
                /** Border */
                border-radius: 25px;

                /** Alignment */
                margin: 50px auto;
            }
            
            #detailLabels {
                /** Width / Height */
                width: 300px;
                height: 300px;
                
                /** Alignment */
                text-align: right;
                margin-left: 50px;
                float: left;

                /** Font Style */
                font-size: 25px;
            }
            
            .detailLabel, .detailValue {
                line-height: 35px;
            }

            #detailValues {
  
                /** Alignment */
                float: left;
                margin-left: 5px;

                /** Width / Height */
                width: 200px;
                height: 300px;

                /** Font Style */
                font-size: 25px;
                font-weight: bold;
            }
            
            .detailTop {
                /** Width / Height */
                width: 600px;
                height: 300px;
            }
            
            .detailImage {
                
                /** Alignment */
                float: left;
                
                /** Width / Height */
                width: 300px;
                height: 300px; 
            }
            
            .detailForecast {
                
                /** Alignment */
                float: left;
                margin-left: 20px;
                margin-top: 100px;

                /** Width / Height */
                width: 280px;
                height: 250px; 
            }
            
            #detailSummary{
                font-size: 40px;
                font-weight: bold;
            }
            
            #detailTemp{
                font-size: 120px;
                font-weight: bold;
            }
            
            #detailDegreeSymbol{
                /** Alignment */
                padding-bottom: 80px;
                margin-left: -30px;
            }
            
            #detailFarenheit {
                font-size: 100px;
                font-weight: bold;
            }
            
            .detailHeader {

                width: 600px;
                text-align: center;

                /** Alignment */
                margin: 50px auto;
                
                font-size: 45px;
                font-weight: bold;
            }
            
            /** Arrow Section */
            .arrow {
                
                /** Width/Height */
                width: 50px;
                height: 50px;

                /** Alignment */
                text-align: center;
                margin: 50px auto;
            }
            
            /** Arrow Up Icon */
            #arrowUpIcon {
                
                opacity: 1;
                display:none;
     
                /** Width/Height */
                width: 50px;
                height: 50px;
            }
            
            /** Arrow Down Icon */
            #arrowDownIcon {
                opacity: 1;
                display:block;
     
                /** Width/Height */
                width: 50px;
                height: 50px;
            }
            
            #graph {
                /** Visiblility */
                display: none;
                
                /** Width/Height */
                width: 800px;
                
                /** Alignment */
                margin: 0 auto;
                text-align: center;
            }
            
            /** Alert Section */
            #alertSection, #errorSection {
                
                /** Width/Height */
                width: 400px;
                height: 25px;
                display: none;

                background-color: #EFEFEF;
                border: 2px solid #9D9D9D;

                /** Alignment */
                margin: 50px auto;
                text-align: center;
            }

            .alertMsg, .errorMsg {

                font-size: 20px;
            }
            
            table {
                border-collapse: collapse;
            }
            
            table, th, td {
                border: 2px solid #2CA0CC;
            }
        </style>
        
        <!-- Script for Google Chart Loading -->
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            
        <!-- Script for Drawing Google Graph -->
        <script type="text/javascript">
            
            // Global Temperature Array
            var tempArray;
            
            /**
             * drawGraph - Draws Google Chart Graph
             */
            function drawGraph()
            {
                var data = new google.visualization.DataTable();
                data.addColumn('number', 'X');
                data.addColumn('number', 'T');
             
                // Iterate over temp array
                for(var i=0; i<tempArray.length; ++i)
                {
                    // Get Temp Array object
                    var obj = tempArray[i];
                    
                    // Iterate over keys in object
                    for(var key in obj)
                    {
                        // Check if Temperature is Key
                        if("temperature" == key)
                        {
                            // Add Temperature to Graph
                            var temp = obj[key];
                            data.addRow([i, temp]);
                        }
                    }
                }
                
                // Set Graph Axes
                var options = { hAxis: {title: 'Time'},
                                vAxis: {title: 'Temperature', textPosition: 'none'},
                                series: {1: {curveType: 'function'}},
                                colors: ['#9DD2DB']};

                // Draw Graph
                var chart = new google.visualization.LineChart(document.getElementById('graph'));
                chart.draw(data, options);
            }
            
            /**
             * loadGraph - Loads Google Chart Graph
             */
            function loadGraph(jsonData)
            {
                // Set Temp Array
                tempArray = jsonData;

                // Create Google Chart
                google.charts.load('current', {packages: ['corechart', 'line']});
                google.charts.setOnLoadCallback(drawGraph);
            }
        </script>

        <!-- Script for State Dropdown Population -->
        <script type="text/javascript">
            function populateStateDropdown()
            {
                // Get the JSON File URL
                var states = '{ "States":[' +
                             '{ "Abbreviation":"AL", "State":"Alabama" },' + 
                             '{ "Abbreviation":"AK", "State":"Alaska" },' + 
                             '{ "Abbreviation":"AZ", "State":"Arizona" },' +
                             '{ "Abbreviation":"AR", "State":"Arkansas" },' + 
                             '{ "Abbreviation":"CA", "State":"California" },' +
                             '{ "Abbreviation":"CO", "State":"Colorado" },' +
                             '{ "Abbreviation":"CT", "State":"Connecticut" },' +
                             '{ "Abbreviation":"DE", "State":"Delaware" },' +
                             '{ "Abbreviation":"DC", "State":"District Of Columbia" },' +
                             '{ "Abbreviation":"FL", "State":"Florida" },' +
                             '{ "Abbreviation":"GA", "State":"Georgia" },' +
                             '{ "Abbreviation":"HI", "State":"Hawaii" },' +
                             '{ "Abbreviation":"ID", "State":"Idaho" },' +
                             '{ "Abbreviation":"IL", "State":"Illinois "},' +
                             '{ "Abbreviation":"IN", "State":"Indiana "},' +
                             '{ "Abbreviation":"IA", "State":"Iowa" },' +
                             '{ "Abbreviation":"KS", "State":"Kansas" },' +
                             '{ "Abbreviation":"KY", "State":"Kentucky" },' +
                             '{ "Abbreviation":"LA", "State":"Louisiana" },' +
                             '{ "Abbreviation":"ME", "State":"Maine" },' +
                             '{ "Abbreviation":"MD", "State":"Maryland" },' +
                             '{ "Abbreviation":"MA", "State":"Massachusetts" },' +
                             '{ "Abbreviation":"MI", "State":"Michigan" },' +
                             '{ "Abbreviation":"MN", "State":"Minnesota" },' +
                             '{ "Abbreviation":"MS", "State":"Mississippi" },' +
                             '{ "Abbreviation":"MO", "State":"Missouri" },' +
                             '{ "Abbreviation":"MT", "State":"Montana" },' +
                             '{ "Abbreviation":"NE", "State":"Nebraska" },' +
                             '{ "Abbreviation":"NV", "State":"Nevada" },' +
                             '{ "Abbreviation":"NH", "State":"New Hampshire" },' +
                             '{ "Abbreviation":"NJ", "State":"New Jersey" },' +
                             '{ "Abbreviation":"NM", "State":"New Mexico" },' +
                             '{ "Abbreviation":"NY", "State":"New York" },' +
                             '{ "Abbreviation":"NC", "State":"North Carolina" },' +
                             '{ "Abbreviation":"ND", "State":"North Dakota" },' +
                             '{ "Abbreviation":"OH", "State":"Ohio" },' +
                             '{ "Abbreviation":"OK", "State":"Oklahoma" },' +
                             '{ "Abbreviation":"OR", "State":"Oregon" },' +
                             '{ "Abbreviation":"PA", "State":"Pennsylvania" },' +
                             '{ "Abbreviation":"RI", "State":"Rhode Island" },' +
                             '{ "Abbreviation":"SC", "State":"South Carolina" },' +
                             '{ "Abbreviation":"SD", "State":"South Dakota" },' +
                             '{ "Abbreviation":"TN", "State":"Tennessee" },' +
                             '{ "Abbreviation":"TX", "State":"Texas" },' +
                             '{ "Abbreviation":"UT", "State":"Utah" },' +
                             '{ "Abbreviation":"VT", "State":"Vermont" },' +
                             '{ "Abbreviation":"VA", "State":"Virginia" },' +
                             '{ "Abbreviation":"WA", "State":"Washington" },' +
                             '{ "Abbreviation":"WV", "State":"West Virginia" },' +
                             '{ "Abbreviation":"WI", "State":"Wisconsin" },' +
                             '{ "Abbreviation":"WY", "State":"Wyoming" } ]}';

                // Parse received JSON data into object
                jsonObj = JSON.parse(states);

                // Get the header key names
                stateList = jsonObj.States;

                // Get State Dropdown
                var stateDropdown = document.getElementById("stateDropdown");
                
                // Check if Dropdown is Empty
                if(stateDropdown.length == 0)
                {
                    // Add "State" as First Option
                    var stateOption = document.createElement("OPTION");
                    stateOption.innerHTML = "State";
                    stateOption.setAttribute("class", "itemChecked");
                    stateDropdown.options.add(stateOption);

                    // Add Separator between First Option and States
                    var separator = document.createElement("OPTION");
                    separator.disabled = true;
                    separator.innerHTML = "-----------------------------------------------";
                    stateDropdown.options.add(separator);

                    // Iterate over all the States
                    for(var i=0; i<stateList.length; i++)
                    {
                        // Create Option
                        var option = document.createElement("OPTION");

                        // Set State Name in Text part
                        option.innerHTML = stateList[i].State;

                        // Set State Abbreviation in Value part
                        option.value = stateList[i].Abbreviation;

                        // Add option to State Dropdown
                        stateDropdown.options.add(option);
                    }
                }

                // Update Dropdown value after POST
                document.getElementById("stateDropdown").value = "<?php echo isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : 'State') ?>";
            }
        </script>

        <!-- Script for Search Callback -->
        <script type="text/javascript">

            function validate()
            {              
                // Get Current Location Checkbox
                var locBox = document.getElementById("locBox");
                
                // Check if Current Location Checkbox is Selected
                if(locBox.checked)
                {
                    // Hide Alert Section
                    document.getElementById("alertSection").style.display = "none";
                    
                    return true;
                }
                else
                {
                    // Get Text Fields
                    var streetTextField = document.getElementById("streetTextField");
                    var cityTextField = document.getElementById("cityTextField");
                    var stateDropdown = document.getElementById("stateDropdown");

                    // Check that Street and City are Valid
                    if((streetTextField.value !== "") && (cityTextField.value !== "") && (stateDropdown.value !== "State"))
                    {
                        // Hide Alert Section
                        document.getElementById("alertSection").style.display = "none";

                        return true;
                    }
                    else
                    {                   
                        // Show Alert Section
                        document.getElementById("alertSection").style.display = "block";
                        
                        // Hide Card/Table Sections
                        document.getElementById("cardView").style.display = "none";
                        document.getElementById("tableView").style.display = "none";
                        document.getElementById("detailSection").style.display = "none";
                        document.getElementById("errorSection").style.display = "none";

                        return false;
                    }
                }
            }
            
            /**
             * clearAll - clears all the fields
             */
            function clearAll()
            {               
                // Clear Text Fields
                document.getElementById("streetTextField").value = "";
                document.getElementById("cityTextField").value = "";
                
                // Reset Dropdown
                document.getElementById("stateDropdown").selectedIndex = "0";
                
                // Reset Checkbox
                document.getElementById("locBox").checked = false;
                
                // Enable Fields
                streetTextField.disabled = false;
                cityTextField.disabled = false;
                stateDropdown.disabled = false;
                
                // Hide Sections
                document.getElementById("cardView").style.display = "none";
                document.getElementById("tableView").style.display = "none";
                document.getElementById("alertSection").style.display = "none";
                document.getElementById("errorSection").style.display = "none";
                document.getElementById("detailSection").style.display = "none";
                
                // Reset URL
                document.location.href="http://hexafire.appspot.com";
            }
            
            /**
             * handleVisibility of Divs
             */
            function handleVisibility()
            {
                <?php 
                    if(isset($_POST['search']))
                    {
                        if($_POST["locBox"] !== null)
                        {
                            // Show Card View
                            echo "document.getElementById(\"cardView\").style.display = \"block\";";
                        }
                        elseif(!empty($_POST["street"]) && !empty($_POST["city"]) && !empty($_POST["state"]) && ($_POST["state"] != "State") && isset($GLOBALS['xml']))
                        {
                            // Show Card View
                            echo "document.getElementById(\"cardView\").style.display = \"block\";";
                        }
                        else
                        {
                            // Hide Card View
                            echo "document.getElementById(\"cardView\").style.display = \"none\";";
                        }
                    }
                    else
                    {
                        // Hide Card View
                        echo "document.getElementById(\"cardView\").style.display = \"none\";";
                    }
                ?>
                
                <?php
                    if(isset($_POST['search']) && !isset($GLOBALS['xml']) && !isset($GLOBALS["locBox"]))
                   {
                        // Show Error Section
                        echo "document.getElementById(\"errorSection\").style.display = \"block\";";
                   }
                    else
                    {
                        // Hide Error Section
                        echo "document.getElementById(\"errorSection\").style.display = \"none\";";
                    }
                ?>

                <?php 
                    if(isset($_POST['search']))
                    {
                        if($_POST["locBox"] !== null)
                        {
                            // Show Card View
                            echo "document.getElementById(\"tableView\").style.display = \"block\";";
                        }
                        elseif(!empty($_POST["street"]) && !empty($_POST["city"]) && !empty($_POST["state"]) && ($_POST["state"] != "State") && isset($GLOBALS['xml']))
                        {
                            // Show Card View
                            echo "document.getElementById(\"tableView\").style.display = \"block\";";
                        }
                        else
                        {
                            // Hide Card View
                            echo "document.getElementById(\"tableView\").style.display = \"none\";";
                        }
                    }
                    else
                    {
                        // Hide Card View
                        echo "document.getElementById(\"tableView\").style.display = \"none\";";
                    }
                ?>
                
                document.getElementById("detailSection").style.display = "<?php echo ((isset($_GET["lat"]) && isset($_GET["lon"]) && isset($_GET["time"]))) ? "block" : "none" ?>";
            }
            
            function handleStartup()
            {
                populateStateDropdown();
                handleCheckBox();
                handleVisibility();
            }
        </script>
        
        <!-- Script for Viewing JSON -->
        <script type="text/javascript">
            function getCurrentLocation()
            {                
                // Get the JSON File URL
                var url = "http://ip-api.com/json";
                
                // Create data request object
                var xmlhttp = new XMLHttpRequest();
                
                // Check if Status changes on Request
                xmlhttp.onreadystatechange = function() 
                {    
                    // If a valid response is ready
                    if(this.readyState == 4 && this.status == 200)
                    {
                        try
                        {
                            // Check if Filename Entered
                            if(url == "") { throw "No File Entered. Please Enter a Filename."}
                            
                            // Parse received JSON data into object
                            jsonObj = JSON.parse(xmlhttp.responseText);

                            // Update Latitude/Longitude
                            document.getElementById("lat").value = jsonObj.lat;
                            document.getElementById("lon").value = jsonObj.lon;
                            document.getElementById("hiddenCity").value = jsonObj.city;
                        }
                        catch(err)
                        {
                            // Log Error Statement in Alert Box
                            console.log(err + url)
                        }
                    }
                    else if(this.readyState == 4 && this.status != 200)
                    {
                        // Log Error Statement in Alert Box
                        console.log("File Not Found: " + url);
                    }
                }
                
                // Get the specified file --Synchronous--
                xmlhttp.open("GET", url, false);
                
                // Send Request to server
                xmlhttp.send();
            }
        </script>

        <script type="text/javascript">
            function handleDownArrow()
            {
                document.getElementById("arrowDownIcon").style.display = "none";
                document.getElementById("arrowUpIcon").style.display = "block";
                document.getElementById("graph").style.display = "block";
                drawGraph();
            }
            
            function handleUpArrow()
            {
                document.getElementById("arrowDownIcon").style.display = "block";
                document.getElementById("arrowUpIcon").style.display = "none";
                document.getElementById("graph").style.display = "none";
            }
            
            function handleCheckBox()
            {   
                // Get CheckBox
                var checkbox = document.getElementById("locBox");

                // Get Fields
                var streetTextField = document.getElementById("streetTextField");
                var cityTextField = document.getElementById("cityTextField");
                var stateDropdown = document.getElementById("stateDropdown");

                // If Location Checkbox Selected 
                if(checkbox.checked)
                {
                    // Get the Current Location
                    getCurrentLocation();
                    
                    // Clear Text Fields
                    document.getElementById("streetTextField").value = "";
                    document.getElementById("cityTextField").value = "";

                    // Reset Dropdown
                    document.getElementById("stateDropdown").selectedIndex = "0";
                }
                else
                {
                    // Set Values to Null
                    document.getElementById("lat").value = null;
                    document.getElementById("lon").value = null;
                    document.getElementById("hiddenCity").value = null;
                }
                  
                // Enable/Disable Fields
                streetTextField.disabled = checkbox.checked;
                cityTextField.disabled = checkbox.checked;
                stateDropdown.disabled = checkbox.checked;
            }
        </script>
    </head>
<body onload="handleStartup()">
    <div class="page">
        <h1 class=""> Weather Search </h1>
        <form name="myForm" method="post" action="main.php" onsubmit="return validate();" id="location">
            <div class="mainSection">
                <div class="dataEntrySection">
                    <div class="block">
                        <label class="dataEntryLabel">Street</label>
                        <input id="streetTextField" type="text" name="street" value="<?php echo isset($_POST['street']) ? $_POST['street'] : (isset($_GET['street']) ? $_GET['street'] : '') ?>" />
                    </div>
                    <div class="block">
                        <label class="dataEntryLabel">City</label>
                        <input id="cityTextField" type="text" name="city" value="<?php echo isset($_POST['city']) ? $_POST['city'] : (isset($_GET['city']) ? $_GET['city'] : '')?>"/>
                    </div>
                    <div class="block">
                        <label class="dropdownLabel">State</label>
                        <select id="stateDropdown" name="state">State</select>
                    </div>
                </div>
                <div class="lineSection">
                    <div class="vLine"></div> 
                </div>
                <div class="checkBoxSection">
                    <label class="currentLocationBox"><input id="locBox" type="checkbox" name="locBox" onchange="handleCheckBox()"
                    <?php if(isset($_POST['locBox']) || isset($_GET['locBox'])) echo "checked='checked'"; ?>>Current Location</label>
                    <input id="lat" type="text" name="lat" hidden />
                    <input id="lon" type="text" name="lon" hidden />
                    <input id="hiddenCity" type="text" name="hiddenCity" hidden />
                </div>

                <div class="buttonSection">
                    <br/><br/>
                    <button class="searchButton" type="submit" name="search">search</button>
                    <button class="clearButton"  type="button" name="clear" onClick="clearAll();">clear</button>
                </div>
            </div>
        </form>
    </div>
    <div id="alertSection">
        <label class="alertMsg">Please check the input address.</label> 
    </div>
        <div id="errorSection">
        <label class="errorMsg">Please enter a valid input address.</label> 
    </div>
    <div id="cardView">
        <div class="cardLabels">
            <br>
            <label id="city"> <?php echo $city ?> </label><br>
            <label id="timezone"> <?php echo $timezone ?> </label><br>
            <label id="temperature"> <?php echo $temperature ?> </label>
                <img id="degreeSymbol" src="https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png" width="12" height="12">
            <label id="farenheit">&nbsp; F</label><br>
            <label id="summary"><?php echo $summary ?> </label>
        </div>
        <div class="cardIcons">
            <?php
                // Null Check Humidity
                if(!is_null($resultArray['currently']['humidity']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-16-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> Humidity </span>";
                    echo "</div>";
                }
                // Null Check Pressure
                if(!is_null($resultArray['currently']['pressure']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-25-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> Pressure </span>";
                    echo "</div>";
                }
                // Null Check Wind Speed
                if(!is_null($resultArray['currently']['windSpeed']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> WindSpeed </span>";
                    echo "</div>";
                }
                // Null Check Visibility
                if(!is_null($resultArray['currently']['visibility']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-30-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> Visibility </span>";  
                    echo "</div>";
                }
                // Null Check Cloud Cover
                if(!is_null($resultArray['currently']['cloudCover']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> Cloud Cover </span>";
                    echo "</div>";
                }
                // Null Check Ozone
                if(!is_null($resultArray['currently']['ozone']))
                {
                    echo "<div class=\"tooltip\">";
                    echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-24-512.png\" width=\"30\" height=\"30\">";
                    echo "<span class=\"iconText\"> Ozone </span>";
                    echo "</div>";
                }
            ?>
        </div>

        <div class="cardValues">
            <?php
                // Null Check Humidity
                if(!is_null($resultArray['currently']['humidity']))
                {
                    echo "<label class=\"cardValue\" id=\"humidity\">";
                    echo $resultArray['currently']['humidity'];
                    echo "</label>";
                } 
                // Null Check Pressure
                if(!is_null($resultArray['currently']['pressure']))
                {
                    echo "<label class=\"cardValue\" id=\"pressure\">";
                    echo $resultArray['currently']['pressure'];
                    echo "</label>";
                } 
                // Null Check Wind Speed
                if(!is_null($resultArray['currently']['windSpeed']))
                {
                    echo "<label class=\"cardValue\" id=\"windSpeed\">";
                    echo $resultArray['currently']['windSpeed'];
                    echo "</label>";
                } 
                // Null Check Visibility
                if(!is_null($resultArray['currently']['visibility']))
                {
                    echo "<label class=\"cardValue\" id=\"visibility\">";
                    echo $resultArray['currently']['visibility'];
                    echo "</label>";
                } 
                // Null Check Cloud Cover
                if(!is_null($resultArray['currently']['cloudCover']))
                { 
                    echo "<label class=\"cardValue\" id=\"cloudCover\">";
                    echo $resultArray['currently']['cloudCover']; 
                    echo "</label>";
                }
                // Null Check Ozone
                if(!is_null($resultArray['currently']['ozone']))
                { 
                    echo "<label class=\"cardValue\" id=\"ozone\">";
                    echo $resultArray['currently']['ozone']; 
                    echo "</label>";
                } 
            ?>
        </div>
    </div>
    <div id="tableView">
        <table style="width:100%">
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Summary</th>
                <th>TemperatureHigh</th>
                <th>TemperatureLow</th>
                <th>Wind Speed</th>
            </tr>
            <?php

                // Iterate over each Day
                foreach($GLOBALS['dataArray'] as $item)
                {
                    // Create Table Row
                    echo "<tr>";

                    // Get Date Value
                    echo "<td>";
                    $epoch = $item['time'];
                    $dt = new DateTime("@$epoch");
                    echo $dt->format('Y-m-d');
                    echo "</td>";

                    // Get the Status Icon
                    echo "<td>";
                    if(($item['icon'] == "clear-day") || ($item['icon'] == "clear-night"))
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-12-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "rain")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-04-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "snow")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-19-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "sleet")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-07-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "wind")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-27-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "fog")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-28-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif($item['icon'] == "cloudy")
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-01-512.png\" width=\"30\" height=\"30\">";
                    }
                    elseif(($item['icon'] == "partly-cloudy-day") || ($item['icon'] == "partly-cloudy-night"))
                    {
                        echo "<img class=\"icon\" src=\"https://cdn2.iconfinder.com/data/icons/weather-74/24/weather-02-512.png\" width=\"30\" height=\"30\">";
                    }
                    echo "</td>";

                    // Get the Summary Value
                    if(!empty($GLOBALS['locBox']))
                    {
                        echo "<td onClick=\"document.location.href='http://hexafire.appspot.com/main.php?lat=$lat&lon=$lon&time=$epoch&locBox=$locBox'\">";
                        echo $item['summary'];
                        echo "</td>";
                    }
                    else
                    {
                        echo "<td onClick=\"document.location.href='http://hexafire.appspot.com/main.php?lat=$lat&lon=$lon&time=$epoch&street=$street&city=$city&state=$state'\">";
                        echo $item['summary'];
                        echo "</td>";
                    }

                    // Get the High Temperature Value
                    echo "<td>";
                    echo round($item['temperatureHigh']);
                    echo "</td>";

                    // Get the Low Temperature Value
                    echo "<td>";
                    echo round($item['temperatureLow']);
                    echo "</td>";

                    // Get the Wind Speed Value
                    echo "<td>";
                    echo $item['windSpeed'];
                    echo "</td>";

                    echo "</tr>";
                }
            ?>
        </table>
    </div>
    <div id="detailSection">
        <div class="detailHeader">
            <label class="weatherDetail">Daily Weather Detail</label>
        </div>
        <div class="detailView">
            <div class="detailTop">
                <div class="detailForecast">
                    <label id="detailSummary"> <?php echo $GLOBALS['detailSummary'] ?> </label> <br>
                    <label id="detailTemp"> <?php echo $detailTemp ?> </label>
                    <img id="detailDegreeSymbol" src="https://cdn3.iconfinder.com/data/icons/virtual-notebook/16/button_shape_oval-512.png" width="12" height="12"> 
                    <label id="detailFarenheit"> F</label>
                </div>
                <div class="detailImage">
                    <?php
                        if(($detailIcon == "clear-day") || ($detailIcon == "clear-night"))
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/sun-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "rain")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/rain-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "snow")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/snow-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "sleet")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/lightning-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "wind")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn4.iconfinder.com/data/icons/the-weather-is-nice-today/64/weather_10512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "fog")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/cloudy-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif($detailIcon == "cloudy")
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/cloud-512.png\" width=\"300\" height=\"300\">";
                        }
                        elseif(($detailIcon == "partly-cloudy-day") || ($detailIcon == "partly-cloudy-night"))
                        {
                            echo "<img class=\"detailIcon\" src=\"https://cdn3.iconfinder.com/data/icons/weather-344/142/sunny-512.png\" width=\"300\" height=\"300\">";
                        }
                    ?>
                </div>
            </div>
            <div class="detailBottom">
                <div id="detailLabels">
                    <label class="detailLabel"> Precipitation: </label> <br>
                    <label class="detailLabel"> Chance of Rain: </label> <br>
                    <label class="detailLabel"> Wind Speed: </label> <br>
                    <label class="detailLabel"> Humidity: </label> <br>
                    <label class="detailLabel"> Visibility: </label> <br>
                    <label class="detailLabel"> Sunrise / Sunset: </label>
                </div>
                <div id="detailValues">
                    <label class="detailValue">
                        <?php
                                if($GLOBALS['precipIntensity'] <= 0.001)
                                {
                                    echo "None";
                                }
                                elseif(($GLOBALS['precipIntensity'] <= 0.015) && ($GLOBALS['precipIntensity'] > 0.001))
                                {
                                    echo "Very Light";
                                }
                                elseif(($GLOBALS['precipIntensity'] <= 0.05) && ($GLOBALS['precipIntensity'] > 0.015))
                                {
                                    echo "Light";
                                }
                                elseif(($GLOBALS['precipIntensity'] <= 0.1) && ($GLOBALS['precipIntensity'] > 0.05))
                                {
                                    echo "Moderate";
                                }
                                elseif($GLOBALS['precipIntensity'] > 0.1)
                                {
                                    echo "Heavy";
                                }

                       ?>
                    </label> <br>
                    <label class="detailValue"><?php echo $precipProbability ?>%</label> <br>
                    <label class="detailValue"><?php echo $detailWindSpeed ?> mph</label> <br>
                    <label class="detailValue"><?php echo $detailHumidity ?>%</label> <br>
                    <label class="detailValue"><?php echo $detailVisibility ?> mi</label><br>
                    <label class="detailValue">
                        <?php
                                $timezone = new DateTimeZone($GLOBALS['detailTimezone']);
                                $sunriseTime =  $GLOBALS['sunriseTime'];
                                $sunrise = new DateTime("@$sunriseTime");
                                $sunrise->setTimezone($timezone);
                                echo $sunrise->format('g A');
                                echo "/ ";
                                $sunsetTime = $GLOBALS['sunsetTime'];
                                $sunset = new DateTime("@$sunsetTime");
                                $sunset->setTimezone($timezone);
                                echo $sunset->format('g A');
                        ?>
                    </label>
                </div>
            </div>
        </div>
        <div id="hourlyView">
            <div class="detailHeader">
                <label class="weatherDetail">Day's Hourly Weather</label>
            </div>
            <div class="arrow">
                <img id="arrowUpIcon" onClick="handleUpArrow()" src="https://cdn0.iconfinder.com/data/icons/navigation-set-arrows-part-one/32/ExpandLess-512.png">
                <img id="arrowDownIcon" onClick="handleDownArrow()" src="https://cdn4.iconfinder.com/data/icons/geosm-e-commerce/18/point-down-512.png">
            </div>
            <?php
                // Store Temperature Array
                $temp = json_encode($detailArray['hourly']['data'], true);

                echo "<div id=\"graph\">";
                    echo "<script>";
                        echo "loadGraph($temp)";
                    echo "</script>";
                    echo "<br><br>";
                echo "</div>";
            ?>
        </div>
    </div>
</body>
</html>