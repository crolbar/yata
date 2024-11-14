<?php
?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>login</title>
        <link rel="stylesheet" href="tailwind.css">
    </head>
    <body>
        <div class="flex items-center justify-center h-screen">
            <button class="text-blue-600 visited:text-purple-600" id="google-button">Login with google</button>
        </div>

        <script>
            let googleButton = document.getElementById('google-button')

            googleButton.addEventListener('click', () => {
                window.location.pathname = "/login/google-oauth"
            })
        </script>
    </body>
</html>

