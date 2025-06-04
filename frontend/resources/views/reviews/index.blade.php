@extends('layouts.app')

@section('title', 'Dana Reviews Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Dana App Reviews</h3>
                    <div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#scrapeModal">
                            <i class="fas fa-download"></i> Scrape New Reviews
                        </button>
                        <a href="{{ route('reviews.export', request()->query()) }}" class="btn btn-info">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Reviews</h5>
                                    <h3>{{ number_format($stats['total']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Average Rating</h5>
                                    <h3>{{ number_format($stats['avg_rating'], 1) }}/5</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Rating Breakdown</h6>
                                    @for($i = 5; $i >= 1; $i--)
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="me-2">{{ $i }} ⭐</span>
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                @php
                                                    $count = $stats['rating_breakdown'][$i] ?? 0;
                                                    $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="text-muted">{{ $count }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <form method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <select name="rating" class="form-select">
                                <option value="">All Ratings</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                        {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search reviews..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('reviews.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                    
                    <!-- Reviews Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Rating</th>
                                    <th>Date</th>
                                    <th>Review</th>
                                    <th>Reply</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>{{ $review->user_name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $review->score >= 4 ? 'success' : ($review->score >= 3 ? 'warning' : 'danger') }}">
                                                {{ $review->score }}/5 ⭐
                                            </span>
                                        </td>
                                        <td>{{ $review->formatted_date }}</td>
                                        <td>
                                            <div style="max-width: 300px;">
                                                {{ Str::limit($review->content, 150) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($review->reply_content)
                                                <div style="max-width: 200px;">
                                                    {{ Str::limit($review->reply_content, 100) }}
                                                </div>
                                            @else
                                                <span class="text-muted">No reply</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('reviews.show', $review->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('reviews.destroy', $review->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No reviews found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    {{ $reviews->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scrape Modal -->
<div class="modal fade" id="scrapeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scrape New Reviews</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scrapeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="app_id" class="form-label">App ID</label>
                        <input type="text" class="form-control" id="app_id" name="app_id" value="id.dana" required>
                        <small class="form-text text-muted">Package name dari aplikasi di Play Store</small>
                    </div>
                    <div class="mb-3">
                        <label for="count" class="form-label">Number of Reviews</label>
                        <input type="number" class="form-control" id="count" name="count" value="1000" min="1" max="5000" required>
                        <small class="form-text text-muted">Maksimal 5000 reviews per scraping</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="scrapeBtn">
                        <i class="fas fa-download"></i> Start Scraping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('scrapeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('scrapeBtn');
    const originalText = btn.innerHTML;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scraping...';
    
    // Get form data
    const formData = new FormData(this);
    
    // Send AJAX request
    fetch('{{ route("reviews.scrape") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Success: ' + data.message);
            location.reload(); // Refresh page to show new data
        } else {
            alert('Error: ' + data.message);
        }
    })
    
    
    .catch(error => {
    // Log error secara detail ke console
    console.error('Scraping Error Details:', {
        message: error.message,
        stack: error.stack,
        name: error.name,
        status: error.response?.status,
        statusText: error.response?.statusText,
        data: error.response?.data,
        config: error.config
    });

    // Pesan error yang lebih spesifik untuk user
    let userMessage = 'An error occurred while scraping reviews';
    
    if (error.response) {
        // Error dari response HTTP
        userMessage = `Server responded with status ${error.response.status}`;
        if (error.response.status === 404) {
            userMessage = 'The review page was not found (404)';
        } else if (error.response.status === 403) {
            userMessage = 'Access forbidden - you might need to check permissions';
        }
    } else if (error.request) {
        // Request dibuat tapi tidak ada response
        userMessage = 'No response received from server - network issue?';
    } else if (error.message.includes('CORS')) {
        // Masalah CORS
        userMessage = 'Cross-origin request blocked - check server CORS settings';
    } else {
        // Error lainnya
        userMessage = `Error: ${error.message}`;
    }

    // Tampilkan alert atau gunakan UI yang lebih baik
    alert(userMessage); 
})

    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('scrapeModal')).hide();
    });
});
</script>
@endsection