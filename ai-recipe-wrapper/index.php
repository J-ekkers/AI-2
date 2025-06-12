<?php
require_once 'config/config.php';
require_once 'classes/AIWrapper.php';

$recipe = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ingredients'])) {
    try {
        $ingredients = explode(',', $_POST['ingredients']);
        $ingredients = array_map('trim', $ingredients);
        $wrapper = new AIWrapper(OPENAI_API_KEY);
        $recipe = $wrapper->generateRecipe($ingredients);
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Recept Generator</title>
    <style>
        body {
            background: #f6f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 28px 28px 28px;
        }
        h1 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            font-weight: 500;
            color: #4a5568;
        }
        textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px;
            font-size: 1rem;
            margin-top: 6px;
            margin-bottom: 10px;
            resize: vertical;
        }
        button {
            background: #3182ce;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #2563eb;
        }
        .message {
            margin-top: 24px;
            padding: 18px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #222;
            font-size: 1.05rem;
        }
        .message.error {
            background: #ffe5e5;
            color: #b91c1c;
            border: 1px solid #f87171;
        }
        .recipe-card {
            background: #f9fafb;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 24px 20px;
            margin-top: 28px;
        }
        .recipe-card h2 {
            margin-top: 0;
            color: #2563eb;
        }
        .recipe-details {
            display: flex;
            gap: 24px;
            margin-bottom: 12px;
        }
        .recipe-details span {
            background: #e0e7ef;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.98rem;
            color: #374151;
        }
        .recipe-section {
            margin-top: 18px;
        }
        ul, ol {
            margin: 0 0 0 18px;
        }
    </style>
</head>
   <body>
    <div class="container">
     <h1>AI Recept Generator</h1>
     <p>Voer hieronder je ingrediënten in en ontvang een recept!</p>
      <form action="process.php" method="POST">
       <div class="form-group">
        <label for="ingredients">Ingrediënten ( gescheiden door komma 's ):</label>
           <textarea id="ingredients" name="ingredients" rows="4" required
                     placeholder="bijv. ui, knoflook, tomaat, pasta"></textarea>
       </div>
          <button type="submit">Genereer Recept</button>
      </form>

        <?php if (isset($_GET['message'])): ?>
            <?php
            $msg = $_GET['message'];
            $isError = (strpos($msg, 'Fout:') === 0 || strpos($msg, 'Error:') === 0);
            $recipe = null;
            if (!$isError) {
                $json = json_decode($msg, true);
                if ($json && isset($json['naam'])) {
                    $recipe = $json;
                }
            }
            ?>
            <?php if ($isError || !$recipe): ?>
                <div class="message<?php echo $isError ? ' error' : ''; ?>">
                    <pre style="margin:0; background:none; border:none; font-family:inherit; white-space:pre-wrap;"><?php echo htmlspecialchars($msg); ?></pre>
                </div>
            <?php else: ?>
                <div class="recipe-card">
                    <h2><?php echo htmlspecialchars($recipe['naam']); ?></h2>
                    <div class="recipe-details">
                        <span><strong>Bereidingstijd:</strong> <?php echo htmlspecialchars($recipe['bereidingstijd']); ?></span>
                        <span><strong>Moeilijkheid:</strong> <?php echo htmlspecialchars($recipe['moeilijkheidsgraad']); ?></span>
                    </div>
                    <div class="recipe-section">
                        <strong>Ingrediënten:</strong>
                        <ul>
                            <?php foreach ($recipe['ingrediënten'] as $ing): ?>
                                <li><?php echo htmlspecialchars($ing); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="recipe-section">
                        <strong>Bereidingswijze:</strong>
                        <ol>
                            <?php foreach ($recipe['stappen'] as $stap): ?>
                                <li><?php echo htmlspecialchars($stap); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
   </body>
</html>