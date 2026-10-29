<?php

require_once __DIR__ . '/../controller/BaseController.php';
$modules = [];
foreach (BaseController::modules() as $key => $module) {
    $modules[$key] = [$module['title'], $module['description'], array_column($module['fields'], 0)];
}
$modules['reportes'] = ['Reportes', 'Consulta la actividad y los ingresos del taller', ['Tipo de reporte', 'Desde', 'Hasta']];
return $modules;
