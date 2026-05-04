<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Fetch weather for a given project and date using Open-Meteo (Free, No Key required)
     * 
     * @param Project $project
     * @param string|Carbon $date
     * @return array|null Returns ['condition' => 'sunny|cloudy|rainy|stormy']
     */
    public function getHistoricalWeather(Project $project, $date)
    {
        if (empty($project->location)) {
            return null;
        }

        try {
            $parsedDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            $dateString = $parsedDate->format('Y-m-d');
            $isHistorical = $parsedDate->isPast() && !$parsedDate->isToday();

            // 1. Geocode location
            $geocodeResponse = Http::timeout(5)->get('https://geocoding-api.open-meteo.com/v1/search', [
                'name' => $project->location,
                'count' => 1,
            ]);

            if (!$geocodeResponse->successful() || empty($geocodeResponse->json('results'))) {
                return null;
            }

            $lat = $geocodeResponse->json('results.0.latitude');
            $lon = $geocodeResponse->json('results.0.longitude');

            // 2. Fetch Weather
            if ($isHistorical) {
                // Historical Data
                $weatherResponse = Http::timeout(5)->get('https://archive-api.open-meteo.com/v1/archive', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'start_date' => $dateString,
                    'end_date' => $dateString,
                    'daily' => 'weather_code',
                    'timezone' => 'auto'
                ]);
            } else {
                // Forecast / Today
                $weatherResponse = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'start_date' => $dateString,
                    'end_date' => $dateString,
                    'daily' => 'weather_code',
                    'timezone' => 'auto'
                ]);
            }

            if (!$weatherResponse->successful() || empty($weatherResponse->json('daily.weather_code'))) {
                return null;
            }

            $weatherCode = $weatherResponse->json('daily.weather_code.0');

            return [
                'condition' => $this->mapWeatherCode($weatherCode),
                'source' => 'Open-Meteo API'
            ];

        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Map WMO Weather codes to our application's weather states:
     * sunny, cloudy, rainy, stormy
     */
    protected function mapWeatherCode($code)
    {
        // WMO Weather interpretation codes (WW)
        if (in_array($code, [0, 1])) {
            return 'sunny';
        } elseif (in_array($code, [2, 3, 45, 48])) {
            return 'cloudy';
        } elseif (in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82])) {
            return 'rainy';
        } elseif (in_array($code, [71, 73, 75, 77, 85, 86, 95, 96, 99])) {
            return 'stormy'; // Snow / Thunderstorm considered stormy/extreme for construction
        }

        return 'cloudy'; // fallback
    }
}
