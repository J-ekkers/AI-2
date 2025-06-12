<?php
require_once 'config/config.php';
require_once 'classes/AIWrapper.php';

class AIWrapper {
    private $ingredients = [];
    private $response = '';
    private $apiKey;
    private $model;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct($apiKey = null, $model = 'gpt-3.5-turbo') {
        $this->apiKey = $apiKey ?? (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null);
        $this->model = $model;
        if (!$this->apiKey) {
            throw new Exception('OpenAI API key is not set.');
        }
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
        $prompt = "Geef me een recept op basis van deze ingrediënten: $ingredients.
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
            'temperature' => 0.7
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
    // Voeg deze methode toe aan je RecipeFormatter class
    public function tryExtractRecipe(string $rawOutput): ?Recipe {
    // Eerst proberen als JSON te parsen
    $recipe = $this->formatRecipe($rawOutput);
    if ($recipe) return $recipe;
    // Als dat mislukt, proberen we een minder strenge methode
    // Bijvoorbeeld: reguliere expressies gebruiken om data te extraheren
    $naam = $this->extractName($rawOutput);
    $ingrediënten = $this->extractIngredients($rawOutput);
    // ... andere extracties
    if ($naam && !empty($ingrediënten)) {
    return new Recipe($naam, $ingrediënten, "Onbekend", [], "Onbekend");
    }
    return null;
    }
    // Hulpmethoden voor extractie via regex
    private function extractName($text) { /* ... */ }
    private function extractIngredients($text) { /* ... */ }
    }

// Stuur prompt naar API en ontvang response
$response = $openai->chat([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
]);
$rawOutput = $response->choices[0]->message->content;

// Gebruik de formatter om de output te verwerken
$formatter = new RecipeFormatter();
$recipe = $formatter->formatRecipe($rawOutput);

if ($recipe) {
    // Geldig recept ontvangen, toon aan gebruiker
    displayRecipe($recipe);
} else {
    // Geen geldig recept, toon foutmelding
    echo "Sorry, er ging iets mis bij het genereren van het recept.";
}

function displayRecipe(Recipe $recipe) {
    echo '<div class="recipe">';
    echo '<h6>' . htmlspecialchars($recipe->naam) . '</h6>';
    echo '<div class="recipe-details">';
    echo '<p><strong>Bereidingstijd:</strong> ' . htmlspecialchars($recipe->bereidingstijd) . '</p>';
    echo '<p><strong>Moeilijkheidsgraad:</strong> ' . htmlspecialchars($recipe->moeilijkheidsgraad) . '</p>';
    echo '</div>';
    echo '<h1>Ingrediënten:</h1>';
    echo '<ul>';
    foreach ($recipe->ingrediënten as $ingredient) {
        echo '<li>' . htmlspecialchars($ingredient) . '</li>';
    }
    echo '</ul>';
    echo '<h1>Bereidingswijze:</h1>';
    echo '<ol>';
    foreach ($recipe->stappen as $stap) {
        echo '<li>' . htmlspecialchars($stap) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
}