<?php
// api/core_api.php
// Ce fichier gère la sécurité et le format JSON pour tous les endpoints

// 1. Headers pour autoriser les requêtes (CORS) et définir le JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Inclure le CMS (sans afficher de HTML)
include "../core.php";

// 3. Vérifier si l'API est activée
if ($settings['api_enabled'] != 'Yes') {
    http_response_code(503); // Service Unavailable
    echo json_encode(["status" => "error", "message" => "API is disabled by administrator."]);
    exit;
}

// 4. Vérifier la clé API
$request_key = isset($_GET['key']) ? $_GET['key'] : '';
if ($request_key !== $settings['api_key']) {
    http_response_code(401); // Unauthorized
    echo json_encode(["status" => "error", "message" => "Invalid or missing API Key."]);
    exit;
}

// Fonction Helper pour nettoyer les chemins d'images pour le mobile
function api_image_url($path) {
    global $settings;
    if (empty($path)) return null;
    $path = str_replace('../', '', $path);
    if (strpos($path, 'http') === 0) return $path;
    return $settings['site_url'] . '/' . $path;
}
?>