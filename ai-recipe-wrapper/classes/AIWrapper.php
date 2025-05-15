<?php
class AIWrapper {
    private $ingredients = [];
    private $response = '';
    private $apiKey;
    private $model;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct() {
    // Controleer of config beschikbaar is
        if (!defined('API_KEY')) {
            require_once __DIR__ . '/../config/config.php';
        }
    }

    public function processInput($ingredients) {
        if (empty($ingredients)) {
            throw new Exception("Geen ingrediënten opgegeven");
        }

        $this->ingredients = $ingredients;
        // Later hier API aanroepen
        return true;
    }

    public function getResponse() {
    // Voorlopig een standaard bericht teruggeven
        $ingredientsList = implode(', ', $this->ingredients);
        $this->response = "Recept met $ingredientsList wordt verwerkt";
        return $this->response;
    }


}