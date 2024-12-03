<?php
require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;

$app->before(function (Request $request) use ($app) {
    if ($request->getMethod() === "OPTIONS") {
        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }
});

$app->after(function (Request $request, Response $response) use ($app) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$app->options('{route}', function () {
    return new Response('', 200);
})->assert('route', '.*');

function calculatePercentage($old, $new) {
    if ($old == 0) {
        return 0;
    }
    return round((($new - $old) / $old) * 100, 1);
}

$app->get('/api/statistics/left', function() {
    $data = json_decode(file_get_contents(__DIR__ . '/data/leftChartData.json'), true);
    return json_encode($data);
});

$app->get('/api/statistics/right', function() {
    $data = json_decode(file_get_contents(__DIR__ . '/data/rightChartData.json'), true);
    return json_encode($data);
});

$app->get('/api/statistics/items', function() {
    $data = json_decode(file_get_contents(__DIR__ . '/data/statisticsItems.json'), true);
    return json_encode($data);
});

$app->get('/api/statistics/widgets', function() {
    $data = json_decode(file_get_contents(__DIR__ . '/data/widgetsData.json'), true);

    foreach ($data['widgets'] as &$widget) {
        $values = $widget['values'];
        $sum = array_sum($values);
        $previousValue = isset($values[count($values) - 2]) ? $values[count($values) - 2] : 0;
        $currentValue = $values[count($values) - 1];
        $percentage = calculatePercentage($previousValue, $currentValue);

        $widget['value'] = $sum;
        $widget['percentage'] = round($percentage, 2);
        $widget['percentageColor'] = $percentage >= 0 ? 'text-success' : 'text-danger';
    }

    return json_encode($data);
});


$app->run();
