<!DOCTYPE html>
<html lang="nl">
<head>
 <meta charset="UTF-8">
  <title>AI Recept Generator</title>
   <link rel="stylesheet" href="css/style.css">
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
        <div class="message">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
            <?php endif; ?>
    </div>
   </body>
</html>