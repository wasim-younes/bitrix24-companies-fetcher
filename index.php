<?php
// User should replace this with their REAL Bitrix24 webhook
$webhook_url = 'https://your-company.bitrix24.com/rest/1/your-webhook-code/';

$using_real_webhook = (strpos($webhook_url, 'your-company.bitrix24.com') === false);

$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;

function get_companies_page($page, $per_page) {
    global $webhook_url, $using_real_webhook;
    
    if (!$using_real_webhook) {
        $all_companies = generate_sample_companies();
        $start_index = ($page - 1) * $per_page;
        return array_slice($all_companies, $start_index, $per_page);
    }
    
    $start_position = ($page - 1) * $per_page;
    $url = $webhook_url . 'crm.company.list?start=' . $start_position;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if (!isset($data['result'])) {
        echo "<div class='message error'>Failed to connect to Bitrix24</div>";
        $all_companies = generate_sample_companies();
        $start_index = ($page - 1) * $per_page;
        return array_slice($all_companies, $start_index, $per_page);
    }
    
    return $data['result'];
}

function generate_sample_companies() {
    $companies = [];
    for ($i = 1; $i <= 100; $i++) {
        $companies[] = [
            'ID' => $i,
            'TITLE' => "Company $i",
            'PHONE' => [['VALUE' => '+1234567890']],
            'EMAIL' => [['VALUE' => "contact$i@company.com"]],
            'DATE_CREATE' => date('Y-m-d H:i:s')
        ];
    }
    return $companies;
}

function show_companies_table($companies) {
    if (empty($companies)) {
        echo "<div class='message'>No companies found</div>";
        return;
    }
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Company Name</th><th>Phone</th><th>Email</th><th>Created</th></tr>";
    
    foreach ($companies as $company) {
        $phone = !empty($company['PHONE'][0]['VALUE']) ? $company['PHONE'][0]['VALUE'] : 'No phone';
        $email = !empty($company['EMAIL'][0]['VALUE']) ? $company['EMAIL'][0]['VALUE'] : 'No email';
        $date = !empty($company['DATE_CREATE']) ? date('M j, Y', strtotime($company['DATE_CREATE'])) : 'Unknown';
        
        echo "<tr>";
        echo "<td>" . ($company['ID'] ?? 'N/A') . "</td>";
        echo "<td><strong>" . htmlspecialchars($company['TITLE'] ?? 'No name') . "</strong></td>";
        echo "<td>" . htmlspecialchars($phone) . "</td>";
        echo "<td>" . htmlspecialchars($email) . "</td>";
        echo "<td>" . $date . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

function show_pagination($current_page, $per_page) {
    global $using_real_webhook;
    
    echo "<div class='pagination'>";
    
    if ($current_page > 1) {
        echo "<a href='?page=" . ($current_page - 1) . "' class='page-link'>← Previous</a> ";
    }
    
    for ($i = max(1, $current_page - 2); $i <= $current_page + 2; $i++) {
        if ($i == $current_page) {
            echo "<span class='page-current'>$i</span> ";
        } else {
            echo "<a href='?page=$i' class='page-link'>$i</a> ";
        }
    }
    
    echo "<a href='?page=" . ($current_page + 1) . "' class='page-link'>Next →</a>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bitrix24 Companies</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; border-left: 4px solid #007cba; }
        .message.success { background: #e8f5e9; border-color: #4caf50; }
        .message.error { background: #ffebee; border-color: #f44336; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        .status-box { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ffeaa7; }
        .pagination { margin: 20px 0; text-align: center; }
        .page-link { padding: 8px 12px; margin: 0 5px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; }
        .page-current { padding: 8px 12px; margin: 0 5px; background: #007bff; color: white; border-radius: 4px; }
        .page-link:hover { background: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Companies from Bitrix24</h1>
        
        <div class="status-box">
            <?php if ($using_real_webhook): ?>
                ✅ Connected to real Bitrix24 account
            <?php else: ?>
                📝 Using sample data - replace the webhook URL above with your real Bitrix24 webhook
            <?php endif; ?>
        </div>

        <?php
        $companies = get_companies_page($current_page, $per_page);
        show_companies_table($companies);
        show_pagination($current_page, $per_page);
        
        echo "<div class='message success'>Page $current_page • " . count($companies) . " companies</div>";
        ?>
    </div>
</body>
</html>