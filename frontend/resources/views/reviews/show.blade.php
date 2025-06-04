@extends('layouts.app')

@section('title', 'Review Detail')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Reviews
                </a>
            </div>
            
            <!-- Review Detail Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-comment me-2"></i>
                        Review Detail
                    </h4>
                    <div>
                        <span class="badge bg-{{ $review->score >= 4 ? 'success' : ($review->score >= 3 ? 'warning' : 'danger') }} fs-6">
                            {{ $review->score }}/5 ⭐
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Review Information -->
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h5 class="text-primary">
                                    <i class="fas fa-user me-2"></i>
                                    {{ $review->user_name ?: 'Anonymous User' }}
                                </h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    {{ $review->formatted_date }}
                                </p>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-mobile-alt me-2"></i>
                                    App ID: <code>{{ $review->app_id }}</code>
                                </p>
                            </div>
                            
                            <!-- Review Content -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-quote-left me-2"></i>
                                    Review Content:
                                </h6>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-0" style="white-space: pre-wrap; line-height: 1.6;">{{ $review->content ?: 'No content provided' }}</p>
                                </div>
                            </div>
                            
                            <!-- Developer Reply -->
                            @if($review->reply_content)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-success">
                                        <i class="fas fa-reply me-2"></i>
                                        Developer Reply:
                                    </h6>
                                    <div class="p-3 bg-success bg-opacity-10 border-start border-success border-4 rounded">
                                        <p class="mb-0" style="white-space: pre-wrap; line-height: 1.6;">{{ $review->reply_content }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-muted">
                                        <i class="fas fa-reply me-2"></i>
                                        Developer Reply:
                                    </h6>
                                    <div class="p-3 bg-light rounded text-center">
                                        <i class="fas fa-comment-slash fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No reply from developer</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Sidebar Information -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Review Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Review ID:</small>
                                        <code class="fs-6">{{ $review->review_id }}</code>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Rating:</small>
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->score)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2 fw-bold">{{ $review->score }}/5</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Review Date:</small>
                                        <strong>{{ $review->review_date ? $review->review_date->format('F j, Y') : 'Unknown' }}</strong><br>
                                        <small class="text-muted">{{ $review->review_date ? $review->review_date->format('g:i A') : '' }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Added to Database:</small>
                                        <strong>{{ $review->created_at->format('F j, Y') }}</strong><br>
                                        <small class="text-muted">{{ $review->created_at->format('g:i A') }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Character Count:</small>
                                        <strong>{{ strlen($review->content) }} characters</strong>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Has Reply:</small>
                                        @if($review->reply_content)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Yes
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-times me-1"></i>
                                                No
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-cogs me-2"></i>
                                        Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-info" onclick="copyToClipboard()">
                                            <i class="fas fa-copy me-2"></i>
                                            Copy Review Text
                                        </button>
                                        
                                        <button class="btn btn-warning" onclick="exportSingle()">
                                            <i class="fas fa-download me-2"></i>
                                            Export as JSON
                                        </button>
                                        
                                        <form action="{{ route('reviews.destroy', $review->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100" 
                                                    onclick="return confirm('Are you sure you want to delete this review?')">
                                                <i class="fas fa-trash me-2"></i>
                                                Delete Review
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard() {
    const reviewText = `User: {{ $review->user_name }}
Rating: {{ $review->score }}/5
Date: {{ $review->formatted_date }}
Review: {{ $review->content }}
{{ $review->reply_content ? 'Reply: ' . $review->reply_content : 'No reply' }}`;
    
    navigator.clipboard.writeText(reviewText).then(function() {
        alert('Review text copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

function exportSingle() {
    const reviewData = {
        id: {{ $review->id }},
        review_id: "{{ $review->review_id }}",
        app_id: "{{ $review->app_id }}",
        user_name: "{{ $review->user_name }}",
        score: {{ $review->score }},
        review_date: "{{ $review->review_date ? $review->review_date->toISOString() : '' }}",
        content: {{ json_encode($review->content) }},
        reply_content: {{ json_encode($review->reply_content) }},
        created_at: "{{ $review->created_at->toISOString() }}"
    };
    
    const dataStr = JSON.stringify(reviewData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = 'review_{{ $review->id }}.json';
    link.click();
}
</script>
@endsection