<?php

namespace App\Services;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class DashboardWeatherResolver
{
    /**
     * @return array{condition: string, icon: string, label: string, isFallback: bool}
     */
    public function resolve(Request $request): array
    {
        $ipAddress = $request->ip();

        if (! is_string($ipAddress) || ! $this->isPublicIpAddress($ipAddress)) {
            return $this->fallback();
        }

        $coordinates = $this->resolveCoordinates($ipAddress);

        if ($coordinates === null) {
            return $this->fallback();
        }

        return $this->resolveWeather(...$coordinates) ?? $this->fallback();
    }

    protected function client(): PendingRequest
    {
        return Http::acceptJson()->timeout(5)->retry(2, 200);
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    protected function resolveCoordinates(string $ipAddress): ?array
    {
        try {
            $response = $this->client()->get(sprintf(
                '%s/%s/json/',
                rtrim((string) config('services.weather.ip_geolocation_url'), '/'),
                $ipAddress
            ));
        } catch (Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $latitude = $response->json('latitude');
        $longitude = $response->json('longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return [(float) $latitude, (float) $longitude];
    }

    /**
     * @return array{condition: string, icon: string, label: string, isFallback: bool}|null
     */
    protected function resolveWeather(float $latitude, float $longitude): ?array
    {
        try {
            $response = $this->client()->get((string) config('services.weather.forecast_url'), [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'weather_code,is_day',
                'timezone' => 'auto',
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $weatherCode = $response->json('current.weather_code');
        $isDay = $response->json('current.is_day');

        if (! is_numeric($weatherCode)) {
            return null;
        }

        return $this->mapWeatherCode((int) $weatherCode, (bool) $isDay);
    }

    /**
     * @return array{condition: string, icon: string, label: string, isFallback: bool}
     */
    protected function mapWeatherCode(int $weatherCode, bool $isDay): array
    {
        return match (true) {
            $weatherCode === 0 => [
                'condition' => 'sunny',
                'icon' => $isDay ? 'Sun' : 'MoonStar',
                'label' => $isDay ? 'Sunny' : 'Clear night',
                'isFallback' => false,
            ],
            in_array($weatherCode, [1, 2], true) => [
                'condition' => 'partly_cloudy',
                'icon' => $isDay ? 'CloudSun' : 'CloudMoon',
                'label' => 'Partly cloudy',
                'isFallback' => false,
            ],
            $weatherCode === 3 => [
                'condition' => 'cloudy',
                'icon' => 'Cloud',
                'label' => 'Cloudy',
                'isFallback' => false,
            ],
            in_array($weatherCode, [45, 48], true) => [
                'condition' => 'foggy',
                'icon' => 'CloudFog',
                'label' => 'Foggy',
                'isFallback' => false,
            ],
            in_array($weatherCode, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82], true) => [
                'condition' => 'rainy',
                'icon' => 'CloudRain',
                'label' => 'Rainy',
                'isFallback' => false,
            ],
            in_array($weatherCode, [71, 73, 75, 77, 85, 86], true) => [
                'condition' => 'snowy',
                'icon' => 'CloudSnow',
                'label' => 'Snowy',
                'isFallback' => false,
            ],
            in_array($weatherCode, [95, 96, 99], true) => [
                'condition' => 'stormy',
                'icon' => 'CloudLightning',
                'label' => 'Stormy',
                'isFallback' => false,
            ],
            default => [
                'condition' => 'cloudy',
                'icon' => 'Cloud',
                'label' => 'Cloudy',
                'isFallback' => false,
            ],
        };
    }

    /**
     * @return array{condition: string, icon: string, label: string, isFallback: bool}
     */
    protected function fallback(): array
    {
        return [
            'condition' => 'time_of_day',
            'icon' => 'Sun',
            'label' => 'Time of day',
            'isFallback' => true,
        ];
    }

    protected function isPublicIpAddress(string $ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
