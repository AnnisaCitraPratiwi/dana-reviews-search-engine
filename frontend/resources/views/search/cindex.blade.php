<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DANA Reviews Search Engine</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* [Gaya CSS seperti sebelumnya, tidak diubah] */
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .search-container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        .search-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); padding: 2rem; backdrop-filter: blur(10px); }
        .search-input { border-radius: 50px; border: 2px solid #e9ecef; padding: 1rem 1.5rem; font-size: 1.1rem; transition: all 0.3s ease; }
        .search-input:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .search-btn { border-radius: 50px; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; font-weight: 600; transition: transform 0.2s ease; }
        .search-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .review-card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid #667eea; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .review-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); }
        .rating-stars { color: #ffc107; }
        .similarity-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px; padding: 0.3rem 0.8rem; font-size: 0.8rem; font-weight: 600; }
        .stats-card { background: rgba(255, 255, 255, 0.9); border-radius: 15px; padding: 1.5rem; text-align: center; margin-bottom: 1rem; }
        .loading-spinner { display: none; }
        .no-results { text-align: center; padding: 3rem; color: #6c757d; }
        .header-title { color: white; text-align: center; margin-bottom: 2rem; font-weight: 700; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="search-container">
            <h1 class="header-title">
                <i class="fas fa-search me-3"></i>
                DANA Reviews Search Engine
            </h1>
            
            <!-- Stats Card -->
            <div class="stats-card" id="statsCard">
                <div class="row">
                    <div class="col-md-3">
                        <h4 class="text-primary mb-0" id="totalReviews">-</h4>
                        <small class="text-muted">Total Reviews</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning mb-0" id="avgRating">-</h4>
                        <small class="text-muted">Average Rating</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info mb-0" id="hasReplies">-</h4>
                        <small class="text-muted">With Replies</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success mb-0" id="dataStatus">Ready</h4>
                        <small class="text-muted">System Status</small>
                    </div>
                </div>
            </div>
            
            <!-- Search Form -->
            <div class="search-card">
                <form class="mb-4" id="searchForm">
                    <div class="row align-items-end">
                        <div class="col-md-8 mb-3">
                            <label for="searchInput" class="form-label fw-bold">
                                <i class="fas fa-comment-dots me-2"></i>Cari Review
                            </label>
                            <input type="text" class="form-control search-input" id="searchInput" placeholder="Masukkan kata kunci review yang ingin dicari..." required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="limitSelect" class="form-label fw-bold">Limit</label>
                            <select class="form-select search-input" id="limitSelect">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary search-btn w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Loading Spinner -->
                <div class="loading-spinner text-center" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Mencari review...</p>
                </div>
            </div>

            <!-- Search Results -->
            <div id="searchResults" class="mt-4"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
        });

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        async function loadStats() {
            try {
                const response = await fetch('/api/stats');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalReviews').textContent = data.stats.total_reviews.toLocaleString();
                    document.getElementById('avgRating').textContent = data.stats.average_rating.toFixed(2) + ' ★';
                    document.getElementById('hasReplies').textContent = data.stats.has_replies || 0;
                    document.getElementById('dataStatus').textContent = 'Ready';
                    document.getElementById('dataStatus').className = 'text-success mb-0';
                } else {
                    document.getElementById('dataStatus').textContent = 'Error';
                    document.getElementById('dataStatus').className = 'text-danger mb-0';
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                document.getElementById('dataStatus').textContent = 'Offline';
                document.getElementById('dataStatus').className = 'text-warning mb-0';
            }
        }

        async function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            const limit = document.getElementById('limitSelect').value;
            const resultsContainer = document.getElementById('searchResults');
            const loadingSpinner = document.getElementById('loadingSpinner');

            if (!query) {
                alert('Mohon masukkan kata kunci pencarian');
                return;
            }

            loadingSpinner.style.display = 'block';
            resultsContainer.innerHTML = '';

            try {
                const response = await fetch(`/api/search?query=${encodeURIComponent(query)}&limit=${limit}`);
                const data = await response.json();
                loadingSpinner.style.display = 'none';

                if (data.success && data.total_results > 0) {
                    data.results.forEach(review => {
                        const card = document.createElement('div');
                        card.className = 'review-card';

                        const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);

                        card.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <h5 class="fw-bold">${review.user}</h5>
                                <span class="similarity-badge">${(review.similarity_score * 100).toFixed(0)}% Match</span>
                            </div>
                            <div class="rating-stars mb-2">${stars}</div>
                            <p class="mb-1">${review.comment}</p>
                            <small class="text-muted">${review.date}</small>
                        `;

                        resultsContainer.appendChild(card);
                    });
                } else {
                    resultsContainer.innerHTML = `<div class="no-results">Tidak ditemukan review yang cocok.</div>`;
                }

            } catch (error) {
                loadingSpinner.style.display = 'none';
                console.error('Search error:', error);
                resultsContainer.innerHTML = `<div class="no-results text-danger">Terjadi kesalahan saat mencari data.</div>`;
            }
        }
    </script>
</body>
</html>
