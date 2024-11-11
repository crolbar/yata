<?php
/**
 * @var array $tasks
 */

$styles = [];
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
        <div class="flex flex-col items-center justify-center gap-10">
            <ul>
                <?php foreach ($tasks as $task): ?>
                    <li>
                        <form method="POST">
                            <?php echo $task["title"] ?>

                            <input type="hidden" name='id' value='<?php echo $task["id"] ?>'>
                            <button class="bg-black text-red-800">X</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

            <form method="POST">
                <div class="flex flex-col items-center justify-center gap-5">
                    <input class="bg-black border" name='title' required>
                    <button class="bg-black border p-1">Add task</button>
                </div>
            </form>
        </div>
    </body>
</html>
