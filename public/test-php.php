<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
</head>
<body>
    <h1>PHP Test Page</h1>
    <p>Current time: <?= date('Y-m-d H:i:s') ?></p>
    <p>PHP Version: <?= PHP_VERSION ?></p>
    <p>Math test: 2 + 2 = <?= 2 + 2 ?></p>

    <?php
    echo "<p>This is from echo: Hello World!</p>";
    ?>

    <h2>Server Info</h2>
    <pre><?php print_r($_SERVER); ?></pre>
</body>
</html>
