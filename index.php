<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kalorientracker – Startseite</title>
  <link rel="stylesheet" href="/styles.css" />
  <link rel="icon" type="images/png" href="/images/logo.png">

</head>
<body>
<?php include __DIR__.'/header.php'; ?>

<main class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 40px; padding: 60px 0;">
  <section style="flex: 1; min-width: 300px;">
    <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 12px;">
      Einfach tracken,<br>einfach abnehmen.
    </h1>
    <p style="color: var(--muted); font-size: 17px; line-height: 1.6; max-width: 480px; margin-bottom: 28px;">
      Behalte den Überblick über deine Ernährung.  
      Erfasse Lebensmittel, erstelle eigene Gerichte und sieh genau,  
      wie viele Kalorien du am Tag zu dir nimmst.
    </p>
    <a href="catalog.php" class="button" style="font-size: 16px; padding: 12px 20px; text-decoration: none;">
      Jetzt starten
    </a>
  </section>

  <section style="flex: 1; min-width: 280px; text-align: center;">
    <img src="/images/logo.png" alt="Kalorientracker Logo" style="max-width: 800px; width: 100%; height: auto; border-radius: 20px;">
  </section>
</main>

<footer class="footer">
  © <?= date('Y') ?> Kalorientracker – Dein smarter Ernährungshelfer
</footer>
</body>
</html>
