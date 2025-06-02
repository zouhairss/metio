<?php
// Traitement AJAX PHP
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['city'])) {
    header('Content-Type: application/json');
    $apiKey = "843ad43e7c9b79b6a6709a7d663df4f2";
    $city = urlencode($_GET['city']);

    $urlCurrent = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric&lang=fr";
    $urlForecast = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric&lang=fr";

    $weatherData = json_decode(file_get_contents($urlCurrent), true);
    $forecastData = json_decode(file_get_contents($urlForecast), true);

    if (!$weatherData || $weatherData['cod'] != 200) {
        echo json_encode(['error' => "Ville non trouvée."]);
        exit;
    }

    // Trouver la prévision demain à 12h
    $forecast = "Non disponible";
    foreach ($forecastData['list'] as $entry) {
        if (strpos($entry['dt_txt'], "12:00:00") !== false) {
            $forecast = $entry['weather'][0]['description'] . " - " . $entry['main']['temp'] . "°C";
            break;
        }
    }

    echo json_encode([
        'name' => $weatherData['name'],
        'temp' => $weatherData['main']['temp'],
        'humidity' => $weatherData['main']['humidity'],
        'description' => $weatherData['weather'][0]['description'],
        'forecast' => $forecast
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prévisions Météo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            text-align: center;
            padding: 50px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
        }

        input {
            padding: 10px;
            width: 70%;
        }

        button {
            padding: 10px 20px;
            margin-left: 10px;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Météo en Temps Réel</h1>
        <input type="text" id="city" placeholder="Entrez une ville">
        <button onclick="getWeather()">Rechercher</button>

        <div id="weatherResult" class="result"></div>
    </div>

    <script>
        function getWeather() {
            const city = document.getElementById("city").value;

            if (!city) {
                alert("Veuillez entrer une ville.");
                return;
            }

            fetch(`?ajax=1&city=${encodeURIComponent(city)}`)
                .then(response => response.json())
                .then(data => {
                    const result = document.getElementById("weatherResult");
                    if (data.error) {
                        result.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        return;
                    }

                    result.innerHTML = `
                        <h2>Météo à ${data.name}</h2>
                        <p>🌡 Température : ${data.temp}°C</p>
                        <p>💧 Humidité : ${data.humidity}%</p>
                        <p>🌤 Conditions : ${data.description}</p>
                        <p>📅 Prévision demain : ${data.forecast}</p>
                    `;
                });
        }
    </script>
</body>
</html>
