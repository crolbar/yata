<?php
$agent = $_SERVER["HTTP_USER_AGENT"];

$styles = [ ];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>yo</title>

        <link rel="stylesheet" href="global.css">
        <style> <?php foreach ($styles as $style) {
            require $style;
        } ?> </style>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="flex items-center justify-center h-screen">
            <p>Hello! Your user agent is "<?php echo $agent?>"!</p>
        </div>
    </body>
</html>
