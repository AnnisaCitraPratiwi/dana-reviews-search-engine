<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DANA Reviews Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .review-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
        }
        .similarity-badge {
            background-color: #0d6efd;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .score-badge {
            background-color: #198754;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <h1 class="mb-4 text-center">DANA Reviews Search</h1>
        
        <form action="{{ route('search') }}" method="GET">
            <div class="input-group mb-4">
                <input type="text" 
                       name="query" 
                       class="form-control form-control-lg" 
                       placeholder="Search reviews..." 
                       value="{{ $query ?? '' }}"
                       required>
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        @if(isset($count))
            <div class="mb-3 text-muted">
                Searching through {{ $count }} reviews...
            </div>
        @endif

        @if(isset($results) && count($results) > 0)
            <h3 class="mb-3">Search Results for "{{ $query }}"</h3>
            
            @foreach($results as $review)
                <div class="review-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">{{ $review['userName'] }}</h5>
                        <div>
                            <span class="score-badge me-2">Rating: {{ $review['score'] }}/5</span>
                            <span class="similarity-badge">Relevance: {{ number_format($review['similarity_score'] * 100, 1) }}%</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">{{ $review['at'] }}</p>
                    <p class="mb-0">{{ $review['content'] }}</p>
                </div>
            @endforeach
        @elseif(isset($query))
            <div class="alert alert-info">
                No results found for "{{ $query }}".
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>