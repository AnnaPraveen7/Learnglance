<?php
$data = json_decode(file_get_contents('php://input'), true);

$input_text = escapeshellarg($data['text']);
$command = escapeshellcmd("python summarize.py $input_text");
$output = shell_exec($command);

echo json_encode(['summary' => trim($output)]);
?>

