<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        form { margin: 20px 0; }
        input { padding: 8px; width: 300px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; }
        ul { list-style: none; padding: 0; }
        li { padding: 10px; background: #f4f4f4; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Simple Task Manager</h1>
        
        <!-- Add Task Form -->
        <form method="POST">
            <input type="text" name="task" placeholder="Enter new task" required>
            <button type="submit" name="add">Add Task</button>
        </form>
        
        <h2>Your Tasks:</h2>
        <ul>
            <?php
            // Database configuration
            $host = getenv('DB_HOST') ?: 'mysql-service';
            $dbname = getenv('DB_NAME') ?: 'taskdb';
            $user = getenv('DB_USER') ?: 'taskuser';
            $password = getenv('DB_PASSWORD') ?: 'taskpass';
            
            try {
                // Connect to database
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create table if not exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    description VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Add task
                if (isset($_POST['add']) && !empty($_POST['task'])) {
                    $task = htmlspecialchars($_POST['task']);
                    $stmt = $pdo->prepare("INSERT INTO tasks (description) VALUES (?)");
                    $stmt->execute([$task]);
                    echo "<p style='color:green'>Task added successfully!</p>";
                }
                
                // Delete task
                if (isset($_GET['delete'])) {
                    $id = (int)$_GET['delete'];
                    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
                    $stmt->execute([$id]);
                    echo "<p style='color:red'>Task deleted!</p>";
                }
                
                // Display tasks
                $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
                $tasks = $stmt->fetchAll();
                
                if (empty($tasks)) {
                    echo "<p>No tasks yet. Add one above!</p>";
                } else {
                    foreach ($tasks as $task) {
                        echo "<li>";
                        echo htmlspecialchars($task['description']);
                        echo " <small>(" . $task['created_at'] . ")</small>";
                        echo " <a href='?delete=" . $task['id'] . "' style='color:red; margin-left:15px;'>Delete</a>";
                        echo "</li>";
                    }
                }
                
            } catch(PDOException $e) {
                echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
                echo "<p>Please check if MySQL is running and configured properly.</p>";
            }
            ?>
        </ul>
        
        <hr>
        <p><strong>Application Info:</strong> PHP <?php echo phpversion(); ?> | Connected to MySQL</p>
    </div>
</body>
</html>
