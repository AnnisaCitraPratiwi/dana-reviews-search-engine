<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DANA Reviews Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .review-card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .review-positive {
            border-left: 4px solid #28a745;
        }
        .review-negative {
            border-left: 4px solid #dc3545;
        }
        .review-neutral {
            border-left: 4px solid #ffc107;
        }
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0">DANA Reviews Analyzer</h1>
                    </div>
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Download Sample</h5>
                                        <p class="card-text">Download a sample CSV file to see the expected format.</p>
                                        <a href="{{ route('download-sample') }}" class="btn btn-primary">
                                            Download Sample CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Upload Reviews</h5>
                                        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="mb-3">
                                                <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                Upload CSV
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(isset($reviews) && count($reviews) > 0)
                            <div class="mb-4">
                                <form action="{{ route('search') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" 
                                               name="query" 
                                               class="form-control" 
                                               placeholder="Search reviews..." 
                                               value="{{ $query ?? '' }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            Search
                                        </button>
                                    </div>
                                </form>
                                
                                @if(isset($searchCount))
                                    <div class="mt-2 text-muted">
                                        Found {{ $searchCount }} reviews matching "{{ $query }}"
                                    </div>
                                @else
                                    <div class="mt-2 text-muted">
                                        Showing all {{ count($reviews) }} reviews
                                    </div>
                                @endif
                            </div>
                            
                            <div class="reviews-list">
                                @foreach($reviews as $review)
                                    <div class="card review-card 
                                        @if($review['score'] >= 4) review-positive
                                        @elseif($review['score'] <= 2) review-negative
                                        @else review-neutral @endif">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <h5 class="card-title mb-0">
                                                    {{ $review['userName'] ?? 'Anonymous' }}
                                                </h5>
                                                <div>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= ($review['score'] ?? 0))
                                                            <span class="text-warning">★</span>
                                                        @else
                                                            <span class="text-secondary">★</span>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>
                                            
                                            <h6 class="card-subtitle mb-2 text-muted">
                                                {{ $review['at'] ?? 'No date' }}
                                            </h6>
                                            
                                            <p class="card-text">
                                                @if(isset($query) && !empty($query))
                                                    {!! preg_replace("/(".$query.")/i", '<span class="search-highlight">$1</span>', $review['content'] ?? '') !!}
                                                @else
                                                    {{ $review['content'] ?? '' }}
                                                @endif
                                            </p>
                                            
                                            @if(!empty($review['replyContent']))
                                                <div class="alert alert-info mt-2 p-2">
                                                    <strong>Developer response:</strong>
                                                    {{ $review['replyContent'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <h4>No reviews loaded</h4>
                                <p class="text-muted">
                                    Upload a CSV file containing DANA reviews to get started.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>