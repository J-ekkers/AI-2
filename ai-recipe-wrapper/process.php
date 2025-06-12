<?php
require_once 'config/config.php';
// Inclusief de AIWrapper klasse
require_once 'classes/AIWrapper.php';
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
        $wrapper = new AIWrapper(OPENAI_API_KEY);

        // Verwerk de ingrediënten
        $wrapper->processInput($ingredients);

        // Haal het antwoord op
        $response = $wrapper->getResponse();

        // Stuur terug naar index met antwoord
        header('Location: index.php?message=' . urlencode($response));
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