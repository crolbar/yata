<?php
/**
 * @var string $url
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="refresh" content="0;url=<?php echo $url?>">
        <title>Redirecting...</title>
        <style>
        * {
            background-color: black;
            color: white;
        }
        </style>
    </head>
    <body>
        Redirecting to `<?php echo $url ?>`...
    </body>
</html>
