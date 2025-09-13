<?php
    //Background processes to handle form submissions

    // Path to the JSON file
    $storage = __DIR__ . '/todo.json';

    // Load tasks from file or start with an empty array
    $todoList = file_exists($storage) ? json_decode(file_get_contents($storage), true): [];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'complete') {
            // update the completion status of the task
            foreach ($todoList as &$todoItem) {
                if ($todoItem['id'] === $id) {
                    $todoItem['done'] = !$todoItem['done'];
                }
            }
            unset($todoItem);
        } elseif ($action === 'delete') {
            // remove item
            $todoList = array_filter($todoList, fn($t) => $t['id'] !== $id);
        }else{
            // Add a task
            if (isset($_POST['todoItem']) && trim($_POST['todoItem']) !== '') {
                $todoList[] = [
                    'id' => time(),
                    'name' => htmlspecialchars($_POST['todoItem']),
                    'done' => false
                ];
            }
        }

        // Save back to file
        file_put_contents($storage, json_encode(array_values($todoList), JSON_PRETTY_PRINT));

        // Reload the page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>To-Do List Web Application</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <h1 class="mb-4">To-Do List Web Application</h1>

            <!-- Form for adding a new task -->
            <form method="post" class="mb-4">
                <div class="input-group">
                    <input type="text" name="todoItem" class="form-control" placeholder="Enter a new task">
                    <button class="btn btn-primary" type="submit">Add</button>
                </div>
            </form>

            <!-- Display to-do list if there are existing items on the list -->
            <?php if ($todoList): ?>
                <ul class="list-group">
                    <?php foreach ($todoList as $todoItem): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="<?= $todoItem['done'] ? 'text-decoration-line-through text-muted' : '' ?>">
                                <?= $todoItem['name'] ?>
                            </span>
                            <div class="btn-group">
                                <!-- Form for marking an item as complete -->
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="id" value="<?= $todoItem['id'] ?>">
                                    <button type="submit" class="btn btn-success me-2">Complete</button>
                                </form>

                                <!-- Form for deleting an item -->
                                <form method="post" action="index.php">
                                  <input type="hidden" name="action" value="delete">
                                  <input type="hidden" name="id" value="<?= $todoItem['id'] ?>">
                                  <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <!-- Otherwise, display text to prompt user to add a new task -->
            <?php else: ?>
                <p class="text-muted">No tasks yet, add one above!</p>
            <?php endif; ?>
        </div>
    </body>
</html>
