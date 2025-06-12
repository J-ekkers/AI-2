<?php
require_once __DIR__ . '/../config/config.php';

class AIWrapper {
    private $ingredients = [];
    private $response = '';
    private $apiKey;
    private $model;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct($apiKey = null, $model = 'gpt-3.5-turbo') {
        if ($apiKey === null) {
            if (!defined('OPENAI_API_KEY')) {
                throw new Exception('OpenAI API key is not defined in config.php');
            }
            $this->apiKey = OPENAI_API_KEY;
        } else {
            $this->apiKey = $apiKey;
        }
        
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is empty');
        }
        
        $this->model = $model;
    }

    public function processInput($ingredients) {
        if (empty($ingredients)) {
            throw new Exception("Geen ingrediënten opgegeven");
        }
        $this->ingredients = $ingredients;
        $this->response = $this->generateRecipe($ingredients);
        return true;
    }

    public function getResponse() {
        return $this->response;
    }

    public function generateRecipe($ingredients) {
        if (!is_array($ingredients)) {
            throw new Exception('Ingrediënten moeten als array worden doorgegeven');
        }
        if (count($ingredients) === 0) {
            throw new Exception('Geef minimaal één ingrediënt op');
        }
        $ingredientsList = implode(', ', $ingredients);
        $prompt = "BELANGRIJK! GEBRUIK ALLEEN DE GEGEVEN INGREDIËNTEN. Geef me een recept op basis van deze ingrediënten: $ingredientsList.
Retourneer ALLEEN een JSON object met de volgende structuur:
{
\"naam\": \"[receptnaam]\",
\"ingrediënten\": [\"ingredient1\", \"ingredient2\", ...],
\"bereidingstijd\": \"[tijd in minuten]\",
\"stappen\": [\"stap1\", \"stap2\", ...],
\"moeilijkheidsgraad\": \"[makkelijk/gemiddeld/moeilijk]\"
}";
        return $this->makeApiRequest($prompt);
    }

    private function makeApiRequest($prompt) {
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Je bent een expert chef.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $this->handleResponse($response, $httpCode);
    }

    private function handleResponse($response, $httpCode) {
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $message = isset($error['error']['message']) ?
                $error['error']['message'] : 'Onbekende API fout';
            throw new Exception('API error (Code ' . $httpCode . '): ' . $message);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        if (!isset($decoded['choices'][0]['message']['content'])) {
            throw new Exception('Onverwachte API response structuur');
        }

        return $decoded['choices'][0]['message']['content'];
    }
}

class Recipe {
    public string $naam;
    public array $ingrediënten;
    public string $bereidingstijd;
    public array $stappen;
    public string $moeilijkheidsgraad;

    public function __construct(
        string $naam,
        array $ingrediënten,
        string $bereidingstijd,
        array $stappen,
        string $moeilijkheidsgraad
    ) {
        $this->naam = $naam;
        $this->ingrediënten = $ingrediënten;
        $this->bereidingstijd = $bereidingstijd;
        $this->stappen = $stappen;
        $this->moeilijkheidsgraad = $moeilijkheidsgraad;
    }
}

class RecipeFormatter {
    public function formatRecipe(string $rawOutput): ?Recipe {
        try {
            $data = json_decode($rawOutput, true);
            if (!$data || !isset($data['naam'], $data['ingrediënten'], $data['bereidingstijd'], 
                $data['stappen'], $data['moeilijkheidsgraad'])) {
                return null;
            }
            
            return new Recipe(
                $data['naam'],
                $data['ingrediënten'],
                $data['bereidingstijd'],
                $data['stappen'],
                $data['moeilijkheidsgraad']
            );
        } catch (Exception $e) {
            return null;
        }
    }
}