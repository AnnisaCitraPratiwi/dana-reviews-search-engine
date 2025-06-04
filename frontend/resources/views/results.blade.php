<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - DANA Reviews</title>
    <!-- Directly include assets if they exist -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('js/app.js') }}"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        .search-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .search-form {
            display: flex;
            gap: 0.5rem;
        }
        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .search-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .search-button:hover {
            background-color: #45a049;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .results-count {
            margin: 1rem 0;
            color: #7f8c8d;
        }
        .review-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .review-user {
            font-weight: bold;
            color: #3498db;
        }
        .review-score {
            color: #f39c12;
        }
        .review-date {
            color: #95a5a6;
            font-size: 0.9rem;
        }
        .review-content {
            margin: 1rem 0;
            line-height: 1.6;
        }
        .review-similarity {
            font-size: 0.9rem;
            color: #27ae60;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #3498db