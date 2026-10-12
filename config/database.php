<?php
// ========================================================
// Database Connection Configuration (PDO MySQL)
// ========================================================

$host = 'localhost';
$dbname = 'project_portfolio';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    // Provide a clear, secure error page with guidance
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Database Setup Required</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0b1021; color: #f1f5f9; padding: 40px 20px; line-height: 1.6; }
            .card { max-width: 600px; margin: 40px auto; background: #131c33; border: 1px solid #243457; border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            h1 { color: #f87171; font-size: 24px; margin-top: 0; }
            code { background: #0a0e1c; padding: 3px 8px; border-radius: 6px; color: #38bdf8; font-size: 14px; }
            pre { background: #0a0e1c; padding: 14px; border-radius: 8px; overflow-x: auto; color: #94a3b8; font-size: 13px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Database Connection Failed</h1>
            <p>Could not connect to the MySQL database <code>project_portfolio</code>.</p>
            <p><strong>To resolve this issue:</strong></p>
            <ol>
                <li>Ensure MySQL is running in your XAMPP Control Panel.</li>
                <li>Import <code>database/schema.sql</code> and <code>database/sample_data.sql</code> via phpMyAdmin or the MySQL command line.</li>
                <li>Verify database credentials in <code>config/database.php</code>.</li>
            </ol>
        </div>
    </body>
    </html>
    <?php
    exit;
}
