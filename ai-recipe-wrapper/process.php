<?php
require_once __DIR__ . '/config/config.php';

// Debug: Check if OPENAI_API_KEY is defined
if (!defined('OPENAI_API_KEY')) {
    die('Error: OPENAI_API_KEY is not defined in config.php');
}

// Inclusief de AIWrapper klasse
require_once __DIR__ . '/classes/AIWrapper.php';
// Controleer of het formulier is verzonden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredients'])) {
    try {
    // Valideer en verwerk de ingrediënten
        $ingredientsInput = trim($_POST['ingredients']);
        if (empty($ingredientsInput)) {
            throw new Exception("Geen ingrediënten opgegeven");
        }

        // Splits de ingrediënten op komma's en verwijder witruimte
        $ingredients = array_map('trim', explode(',', $ingredientsInput));

        // Maak een nieuwe instantie van de AIWrapper
        $wrapper = new AIWrapper();

        // Verwerk de ingrediënten en genereer het recept
        $recipe = $wrapper->generateRecipe($ingredients);

        // Stuur terug naar index met antwoord
        header('Location: index.php?message=' . urlencode($recipe));
        exit;
    } catch (Exception $e) {
    // Stuur terug naar index met foutmelding
        header('Location: index.php?message=Fout: ' . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Als het formulier niet correct is verzonden
    header('Location: index.php?message=Ongeldig verzoek');
    exit;
}
?>