<?php
declare(strict_types=1);
$baseUrl = "http://{$_SERVER["HTTP_HOST"]}";
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gamebase-Backend</title>
<?php
echo "<link rel=\"stylesheet\" href=\"{$baseUrl}/views/css/bootstrap.min.css\">";
?>
<style>
    body {
        background-color: rgb(75, 75, 75); 
        color: white;
    }

    a {
        color: white;
    }
</style>